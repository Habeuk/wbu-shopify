<?php

namespace Stephane888\WbuShopify\Traits;

/**
 * Trait pour la gestion des metafields via GraphQL (mutation metafieldsSet).
 *
 * @author stephane
 */
trait MetafieldsGraphQLTrait {

  /**
   * Sauvegarde (crée ou met à jour) un metafield pour un propriétaire donné.
   *
   * La mutation GraphQL "metafieldsSet" est utilisée (upsert).
   *
   * @param string $ownerGid
   *        GID complet du propriétaire (ex: "gid://shopify/Page/123")
   * @param string $key
   *        Clé du metafield (ex: "liste_d_articles")
   * @param mixed $value
   *        Valeur à stocker (tableau → encodé en JSON automatiquement)
   * @param string $type
   *        Type du metafield (ex: "list.article_reference",
   *        "single_line_text_field", …)
   * @param bool $throwOnError
   *        Si true, lève une exception en cas d'erreur (HTTP, GraphQL ou
   *        userErrors)
   * @param string|null $namespace
   *        Espace de noms (par défaut : constante DEFAULT_NAMESPACE ou 'app')
   *
   * @return array Réponse complète de la mutation (structure :
   *         data.metafieldsSet.metafields / userErrors)
   *
   * @throws \Exception Paramètres manquants, erreur JSON, erreur HTTP, erreur
   *         GraphQL ou userErrors.
   */
  protected function saveMetafieldGraphQL(string $ownerGid, string $key, mixed $value, string $type, bool $throwOnError = true, ?string $namespace = null): array {
    if (empty($ownerGid) || empty($key) || empty($type)) {
      throw new \Exception('ownerGid, key et type sont obligatoires pour sauvegarder un metafield.');
    }

    // Gestion du namespace par défaut
    if ($namespace === null) {
      $namespace = $this->namespace;
    }

    // Conversion des tableaux/objets en JSON (pour les types list.*)
    $encodedValue = $value;
    if (is_array($value) || is_object($value)) {
      $encodedValue = json_encode($value, JSON_UNESCAPED_SLASHES);
      if (json_last_error() !== JSON_ERROR_NONE) {
        throw new \Exception('Erreur JSON : ' . json_last_error_msg());
      }
    }

    // Requête GraphQL
    $query = '
      mutation MetafieldsSet($metafields: [MetafieldsSetInput!]!) {
        metafieldsSet(metafields: $metafields) {
          metafields {
            id
            namespace
            key
            value
            type
            jsonValue
          }
          userErrors {
            field
            message
          }
        }
      }
    ';

    $variables = [
      'metafields' => [
        [
          'ownerId' => $ownerGid,
          'namespace' => $namespace,
          'key' => $key,
          'value' => $encodedValue,
          'type' => $type
        ]
      ]
    ];

    // Exécution de la mutation
    try {
      $response = $this->mutation($query, $variables, $throwOnError);
    }
    catch (\Exception $e) {
      throw new \Exception('Erreur lors de la sauvegarde du metafield : ' . $e->getMessage(), 0, $e);
    }

    // Gestion des userErrors
    if ($throwOnError && isset($response['data']['metafieldsSet']['userErrors'])) {
      $errors = $response['data']['metafieldsSet']['userErrors'];
      if (!empty($errors)) {
        $messages = array_map(fn ($e) => '[' . ($e['field'] ?? 'unknown') . '] ' . ($e['message'] ?? 'Unknown error'), $errors);
        throw new \Exception('Erreurs metafield : ' . implode('; ', $messages));
      }
    }

    return $response;
  }
}