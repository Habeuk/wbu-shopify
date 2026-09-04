<?php

namespace Stephane888\WbuShopify\Traits;

use Stephane888\WbuShopify\Exception\WbuShopifyException;

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
      'variables' => $variables === [] ? new \stdClass() : $variables
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
    $dbg = [
      '$mutation' => $mutation,
      '$variables' => $variables
    ];
    \Stephane888\Debug\debugLog::symfonyDebug($dbg, 'mutation_metafields', true);
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
   * Construit un GID Shopify à partir du type et de l'identifiant.
   *
   * @param string $entity_type
   *        Type d'entité (ex: 'page', 'product', 'customer',
   *        'onlinestorearticle')
   * @param int|string $entity_id
   *        Identifiant numérique ou string
   * @return string GID complet (ex: "gid://shopify/Page/123")
   * @throws \Exception Si le type n'est pas pris en charge
   */
  public static function buildGid(string $entity_type, int|string $entity_id): string {
    $type = strtolower($entity_type);
    $gid = '';

    switch ($type) {
      case 'page':
        $gid = "gid://shopify/Page/" . $entity_id;
        break;
      case 'product':
        $gid = "gid://shopify/Product/" . $entity_id;
        break;
      case 'customer':
        $gid = "gid://shopify/Customer/" . $entity_id;
        break;
      case 'order':
        $gid = "gid://shopify/Order/" . $entity_id;
        break;
      case 'onlinestorearticle':
      case 'article': // alias commun
        $gid = "gid://shopify/OnlineStoreArticle/" . $entity_id;
        break;
      case 'collection':
        $gid = "gid://shopify/Collection/" . $entity_id;
        break;
      case 'metafield':
        $gid = "gid://shopify/Metafield/" . $entity_id;
        break;
      case 'blog':
        $gid = "gid://shopify/Blog/" . $entity_id;
        break;
      case 'shop':
        $gid = "gid://shopify/Shop/" . $entity_id;
        break;
      // Ajoutez d'autres types selon vos besoins
      default:
        throw new WbuShopifyException("Type d'entité non pris en charge pour la construction du GID : " . $entity_type);
    }

    return $gid;
  }
}