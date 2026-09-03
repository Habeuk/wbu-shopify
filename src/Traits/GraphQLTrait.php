<?php

namespace Stephane888\WbuShopify\Traits;

/**
 * Trait GraphQLTrait
 *
 * Fournit des méthodes pour interagir avec l'API GraphQL de Shopify.
 * La classe utilisatrice doit posséder :
 * - $shop_domain
 * - $api_key
 * - $secret
 *
 * @author stephane
 */
trait GraphQLTrait {
  /**
   * Version de l'API GraphQL
   * (vous pouvez la surcharger dans la classe si besoin)
   */
  protected $graphqlApiVersion = '2026-07';

  /**
   * Dernière réponse GraphQL brute (décodée)
   *
   * @var array|null
   */
  protected $lastGraphqlResponse = null;

  /**
   * Dernier code HTTP reçu
   *
   * @var int|null
   */
  protected $lastGraphqlHttpCode = null;

  /**
   * Envoie une requête GraphQL à l'API Admin Shopify
   *
   * @param string $query
   * @param array $variables
   * @param bool $throwOnGraphqlErrors
   *        Si true, lève une exception dès qu'il y a des erreurs GraphQL
   * @return array
   * @throws \Exception
   */
  public function graphqlRequest(string $query, array $variables = [], bool $throwOnGraphqlErrors = true): array {
    if (empty($this->shop_domain) || empty($this->api_key) || empty($this->secret)) {
      throw new \Exception('Configuration Shopify manquante pour GraphQL (shop_domain, api_key, secret)');
    }

    $url = 'https://' . $this->shop_domain . '/admin/api/' . $this->graphqlApiVersion . '/graphql.json';

    $payload = json_encode([
      'query' => $query,
      'variables' => empty($variables) ? new \stdClass() : $variables // important
                                                                      // :
                                                                      // variables
                                                                      // vides =
                                                                      // {}
    ]);

    $headers = [
      'Content-Type: application/json; charset=utf-8',
      'Accept: application/json'
    ];

    $ch = curl_init();
    curl_setopt_array($ch,
      [
        CURLOPT_URL => $url,
        CURLOPT_HTTPHEADER => $headers,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_USERPWD => $this->api_key . ':' . $this->secret,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => 2,
        CURLOPT_TIMEOUT => 30
      ]);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    $this->lastGraphqlHttpCode = $httpCode;

    if ($curlError) {
      throw new \Exception('GraphQL cURL error: ' . $curlError);
    }

    $response = json_decode($result, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new \Exception('GraphQL response is not valid JSON. Raw: ' . substr($result, 0, 500));
    }

    $this->lastGraphqlResponse = $response;

    if ($httpCode < 200 || $httpCode > 299) {
      $message = isset($response['errors']) ? json_encode($response['errors']) : 'HTTP ' . $httpCode;
      throw new \Exception('GraphQL HTTP error: ' . $message);
    }

    // Shopify peut renvoyer HTTP 200 avec des erreurs GraphQL
    if ($throwOnGraphqlErrors && !empty($response['errors'])) {
      throw new \Exception('GraphQL errors: ' . json_encode($response['errors'], JSON_UNESCAPED_UNICODE));
    }

    return $response;
  }

  /**
   * Raccourci pour les queries
   */
  public function query(string $query, array $variables = [], bool $throwOnGraphqlErrors = true): array {
    return $this->graphqlRequest($query, $variables, $throwOnGraphqlErrors);
  }

  /**
   * Raccourci pour les mutations
   */
  public function mutation(string $mutation, array $variables = [], bool $throwOnGraphqlErrors = true): array {
    return $this->graphqlRequest($mutation, $variables, $throwOnGraphqlErrors);
  }

  /**
   * Retourne la dernière réponse GraphQL
   */
  public function getLastGraphqlResponse(): ?array {
    return $this->lastGraphqlResponse;
  }

  /**
   * Retourne le dernier code HTTP GraphQL
   */
  public function getLastGraphqlHttpCode(): ?int {
    return $this->lastGraphqlHttpCode;
  }

  // =========================================================================
  // Helpers GID
  // =========================================================================

  /**
   * Extrait l'ID numérique d'un GID
   * "gid://shopify/OnlineStoreArticle/384651100226" → "384651100226"
   */
  public static function extractIdFromGid(string $gid): string {
    $parts = explode('/', $gid);
    return end($parts);
  }

  /**
   * Extrait le type d'un GID
   * "gid://shopify/OnlineStoreArticle/384651100226" → "OnlineStoreArticle"
   */
  public static function extractTypeFromGid(string $gid): string {
    $parts = explode('/', $gid);
    return $parts[3] ?? '';
  }

  /**
   * Construit un GID
   */
  public static function buildGid(string $type, $id): string {
    return 'gid://shopify/' . $type . '/' . $id;
  }

  // =========================================================================
  // Helpers de récupération (les plus utiles)
  // =========================================================================

  /**
   * Récupère un article via son GID
   */
  public function getArticleByGid(string $gid, string $fields = ''): ?array {
    $defaultFields = '
            id
            title
            handle
            contentHtml
            excerpt
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
            author {
                name
            }
        ';

    $queryFields = $fields ?: $defaultFields;

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
   * Récupère plusieurs articles via une liste de GIDs
   * (idéal pour votre metafield list.article_reference)
   */
  public function getArticlesByGids(array $gids, string $fields = ''): array {
    if (empty($gids)) {
      return [];
    }

    $defaultFields = '
            id
            title
            handle
            contentHtml
            excerpt
            summary
            tags
            publishedAt
            image {
                url
                altText
            }
            blog {
                id
                handle
                title
            }
        ';

    $queryFields = $fields ?: $defaultFields;

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
   * Récupère un produit via son GID
   */
  public function getProductByGid(string $gid, string $fields = ''): ?array {
    $defaultFields = '
            id
            title
            handle
            descriptionHtml
            vendor
            productType
            tags
            status
            variants(first: 20) {
                edges {
                    node {
                        id
                        title
                        price
                        sku
                        inventoryQuantity
                        selectedOptions {
                            name
                            value
                        }
                    }
                }
            }
            images(first: 10) {
                edges {
                    node {
                        id
                        url
                        altText
                    }
                }
            }
        ';

    $queryFields = $fields ?: $defaultFields;

    $query = '
            query GetProduct($id: ID!) {
                product(id: $id) {
                    ' . $queryFields . '
                }
            }
        ';

    $response = $this->graphqlRequest($query, [
      'id' => $gid
    ]);

    return $response['data']['product'] ?? null;
  }

  /**
   * Récupère une page via son GID
   */
  public function getPageByGid(string $gid, string $fields = ''): ?array {
    $defaultFields = '
            id
            title
            handle
            body
            bodySummary
            createdAt
            updatedAt
            isPublished
        ';

    $queryFields = $fields ?: $defaultFields;

    $query = '
            query GetPage($id: ID!) {
                page(id: $id) {
                    ' . $queryFields . '
                }
            }
        ';

    $response = $this->graphqlRequest($query, [
      'id' => $gid
    ]);

    return $response['data']['page'] ?? null;
  }

  /**
   * Récupère n'importe quel nœud via son GID (générique)
   */
  public function getNodeByGid(string $gid, string $fields = ''): ?array {
    if (empty($fields)) {
      $fields = '
                id
                ... on Article {
                    title
                    handle
                    blog { id handle }
                }
                ... on Product {
                    title
                    handle
                    status
                }
                ... on Page {
                    title
                    handle
                }
            ';
    }

    $query = '
            query GetNode($id: ID!) {
                node(id: $id) {
                    ' . $fields . '
                }
            }
        ';

    $response = $this->graphqlRequest($query, [
      'id' => $gid
    ]);

    return $response['data']['node'] ?? null;
  }
}