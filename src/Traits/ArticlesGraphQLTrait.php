<?php

namespace Stephane888\WbuShopify\Traits;

/**
 * Trait ArticlesGraphQLTrait
 *
 * Fournit des méthodes spécifiques aux articles utilisant GraphQL
 * Nécessite que la classe utilisatrice utilise
 * Stephane888\WbuShopify\Traits\GraphQLTrait
 *
 * @author stephane
 */
trait ArticlesGraphQLTrait {

  // liste de champs par defaut.
  protected static $defaultFields = '
                id
                title
                handle
                body
                summary
                tags
                publishedAt
                createdAt
                updatedAt
                image {
                    id
                    url
                    altText
                }
                blog {
                    id
                    handle
                    title
                }
            ';

  /**
   * Récupère tous les articles à partir d'une liste de GIDs
   *
   *
   * @param array $gids
   *        Liste des GIDs d'articles
   * @param string $fields
   *        Champs GraphQL personnalisés (optionnel)
   * @return array Liste des articles
   */
  public function getArticlesFromGids(array $gids, string $fields = ''): array {
    if ($gids === []) {
      return [];
    }
    $queryFields = $fields !== '' ? $fields : self::$defaultFields;
    $query = '
            query GetArticles($ids: [ID!]!) {
                nodes(ids: $ids) {
                    ... on Article {
                        ' . $queryFields . '
                    }
                }
            }
        ';

    $response = $this->graphqlRequest($query, [
      'ids' => array_values($gids)
    ]);

    $articles = [];
    if (!empty($response['data']['nodes'])) {
      foreach ($response['data']['nodes'] as $node) {
        if ($node !== null) {
          $articles[] = $node;
        }
      }
    }

    return $articles;
  }

  /**
   * Récupère un article par son GID
   *
   * @param string $gid
   *        Le GID de l'article
   * @param string $fields
   *        Champs GraphQL personnalisés (optionnel)
   * @return array|null L'article ou null
   */
  public function getArticleByGid(string $gid, string $fields = ''): ?array {
    $queryFields = $fields !== '' ? $fields : self::$defaultFields;
    $query = '
            query GetArticle($id: ID!) {
                article(id: $id) {
                    ' . $queryFields . '
                }
            }
        ';

    $response = $this->graphqlRequest($query, [
      'id' => $gid
    ]);
    return $response['data']['article'] ?? null;
  }

  /**
   * Récupère un article à partir de son ID numérique
   * (Sans avoir besoin de l'ID du blog)
   *
   * @param int|string $article_id
   *        ID numérique de l'article
   * @param string $fields
   *        Champs GraphQL personnalisés (optionnel)
   * @return array|null
   */
  public function getArticleByNumericId($article_id, string $fields = ''): ?array {
    $gid = $this->buildGid('OnlineStoreArticle', $article_id);
    return $this->getArticleByGid($gid, $fields);
  }

  /**
   * Récupère plusieurs articles à partir de leurs IDs numériques
   *
   * @param array $article_ids
   *        Liste des IDs numériques
   * @param string $fields
   *        Champs GraphQL personnalisés (optionnel)
   * @return array
   */
  public function getArticlesByNumericIds(array $article_ids, string $fields = ''): array {
    if (empty($article_ids)) {
      return [];
    }

    $gids = [];
    foreach ($article_ids as $id) {
      $gids[] = $this->buildGid('OnlineStoreArticle', $id);
    }

    return $this->getArticlesFromGids($gids, $fields);
  }

  /**
   * Extrait et récupère les articles depuis un metafield "liste_d_articles"
   *
   * @param int $page_id
   *        ID de la page qui contient le metafield
   * @param string $namespace
   *        Namespace du metafield (défaut: "nutribe")
   * @param string $key
   *        Clé du metafield (défaut: "liste_d_articles")
   * @param string $fields
   *        Champs GraphQL à récupérer
   * @return array Liste des articles avec leurs données
   */
  public function getArticlesFromMetafieldList(int $page_id, string $namespace = 'nutribe', string $key = 'liste_d_articles', string $fields = ''): array {
    // 1. Récupérer le metafield
    $metafield = $this->getMetafieldFromPage($page_id, $namespace, $key);

    if (!$metafield || empty($metafield['value'])) {
      return [];
    }

    // 2. Le metafield stocke un JSON de GIDs
    $gids = json_decode($metafield['value'], true);

    if (!is_array($gids) || empty($gids)) {
      return [];
    }

    // 3. Récupérer tous les articles en une seule requête GraphQL
    return $this->getArticlesFromGids($gids, $fields);
  }

  /**
   * Récupère un metafield d'une page (via REST)
   *
   * @param int $page_id
   * @param string $namespace
   * @param string $key
   * @return array|null
   */
  public function getMetafieldFromPage(int $page_id, string $namespace, string $key): ?array {
    // Vérifier que la classe a une méthode pour récupérer les metafields
    if (method_exists($this, 'getMetafieldsFromPage')) {
      $metafields = $this->getMetafieldsFromPage($page_id);
      foreach ($metafields as $mf) {
        if ($mf['namespace'] === $namespace && $mf['key'] === $key) {
          return $mf;
        }
      }
      return null;
    }

    // Fallback : utiliser REST directement
    // À adapter selon votre structure de classes
    $this->path = 'admin/api/' . ($this->apiVersion ?? '2026-07') . '/pages/' . $page_id . '/metafields.json';
    $datas = $this->GetDatas();
    $result = json_decode($datas, true);

    if (empty($result['metafields'])) {
      return null;
    }

    foreach ($result['metafields'] as $mf) {
      if ($mf['namespace'] === $namespace && $mf['key'] === $key) {
        return $mf;
      }
    }

    return null;
  }

  /**
   * Récupère tous les metafields d'une page (via REST)
   *
   * @param int $page_id
   * @return array
   */
  public function getMetafieldsFromPage(int $page_id): array {
    $this->path = 'admin/api/' . ($this->apiVersion ?? '2026-07') . '/pages/' . $page_id . '/metafields.json';
    $datas = $this->GetDatas();
    $result = json_decode($datas, true);
    return $result['metafields'] ?? [];
  }

  /**
   * Récupère les articles avec leurs metafields
   *
   * @param string $gid
   *        GID de l'article
   * @param string $namespace
   *        Namespace des metafields (défaut: "nutribe")
   * @return array|null
   */
  public function getArticleWithMetafields(string $gid, string $namespace = 'nutribe'): ?array {
    $fields = '
            id
            title
            handle
            tags
            image {
                url
                altText
            }
            blog {
                id
                handle
                title
            }
            metafields(namespace: "' . $namespace . '", first: 20) {
                edges {
                    node {
                        key
                        value
                        type
                        description
                    }
                }
            }
        ';

    return $this->getArticleByGid($gid, $fields);
  }

  /**
   * Récupère plusieurs articles avec leurs metafields
   *
   * @param array $gids
   *        Liste des GIDs
   * @param string $namespace
   *        Namespace des metafields
   * @return array
   */
  public function getArticlesWithMetafields(array $gids, string $namespace = 'nutribe'): array {
    $fields = '
            id
            title
            handle
            tags
            image {
                url
                altText
            }
            blog {
                id
                handle
                title
            }
            metafields(namespace: "' . $namespace . '", first: 20) {
                edges {
                    node {
                        key
                        value
                        type
                        description
                    }
                }
            }
        ';

    return $this->getArticlesFromGids($gids, $fields);
  }

  /**
   * Récupère les GIDs depuis un metafield "liste_d_articles"
   *
   * @param int $page_id
   * @param string $namespace
   * @param string $key
   * @return array
   */
  public function getGidsFromMetafieldList(int $page_id, string $namespace = 'nutribe', string $key = 'liste_d_articles'): array {
    $metafield = $this->getMetafieldFromPage($page_id, $namespace, $key);

    if (!$metafield || empty($metafield['value'])) {
      return [];
    }

    $gids = json_decode($metafield['value'], true);
    return is_array($gids) ? $gids : [];
  }
}
