<?php

namespace Stephane888\WbuShopify\ApiRest\Metafields;

use Stephane888\WbuShopify\Exception\WbuShopifyException;

/**
 * Ce trait doit etre ajouter dans une class sui etend la classe
 * Wbu\ApiRest\Shopify
 *
 * @see https://shopify.dev/api/admin-rest/2022-01/resources/metafield#top
 *
 * @author stephane
 *
 */
trait MetafieldsTrait {
  use MetafieldsValidations;
  /**
   * Permet de retourner la reponse brute.
   *
   * @var boolean
   * @deprecated remove before 2x ( pas ncessaire traiter par getRawBody ).
   */
  public $default_ressource = false;

  public function LoadMetafiels() {
    return $this->get();
  }

  /**
   * Logique de sauvegarde par defaut.
   *
   * @param array $metafields
   * @return mixed[]
   */
  public function save(array $metafields) {
    $result = [];
    foreach ($metafields as $metafield) {
      $this->Validated($metafield);
      $result[] = $this->sendMetafields($metafield, $metafield['value_type'], $metafield['namespace']);
    }
    return $result;
  }

  /**
   * Logique de sauvegarde particulier, utilisé par la pluspart de nos API.
   *
   * @param array $metafields
   * @param string $value_type
   * @return mixed
   */
  public function saveMetafields(array $metafields, $value_type = "single_line_text_field") {
    if (!$this->validation($metafields))
      return false;
    // Surcharge du type de valeur si spécifié
    if (!empty($metafields['type_metafield'])) {
      $value_type = $metafields['type_metafield'];
    }

    $id_entity = $metafields['id_entity'];
    $entity_name = $metafields['type']; // le type doit etre remplacer par
                                        // 'entity_name'.
                                        // (Cela prete à confusion avec la clée
                                        // 'type' du metafield)
    switch ($entity_name) {
      case 'blog':
        $this->path = "admin/api/" . self::$ApiVersion . "/blogs/{$id_entity}/metafields.json";
        break;
      case 'article':
        if (empty($metafields['id_parent'])) {
          $this->has_error = true;
          $this->error_msg = "L'id_parent n'est pas défini pour l'article";
          throw new WbuShopifyException("L'id_parent n'est pas défini pour l'article");
        }
        $id_parent = $metafields['id_parent'];
        $this->path = "admin/api/" . self::$ApiVersion . "/blogs/{$id_parent}/articles/{$id_entity}/metafields.json";
        break;
      case 'product':
        $this->path = "admin/api/" . self::$ApiVersion . "/products/{$id_entity}/metafields.json";
        break;
      case 'page':
        $this->path = "admin/api/" . self::$ApiVersion . "/pages/{$id_entity}/metafields.json";
        break;
      case 'collection':
      case 'custom_collection':
      case 'smart_collection':
        $this->path = "admin/api/" . self::$ApiVersion . "/collections/{$id_entity}/metafields.json";
        break;
      case 'customer':
        $this->path = "admin/api/" . self::$ApiVersion . "/customers/{$id_entity}/metafields.json";
        break;
      case 'order':
        $this->path = "admin/api/" . self::$ApiVersion . "/orders/{$id_entity}/metafields.json";
        break;
      case 'draft_order':
        $this->path = "admin/api/" . self::$ApiVersion . "/draft_orders/{$id_entity}/metafields.json";
        break;
      case 'variant':
        $this->path = "admin/api/" . self::$ApiVersion . "/variants/{$id_entity}/metafields.json";
        break;
      default:
        $this->has_error = true;
        $this->error_msg = "Le type '{$entity_name}' n'est pas encore pris en charge pour les metafields";
        throw new WbuShopifyException("Le type '{$entity_name}' n'est pas encore pris en charge pour les metafields");
        break;
    }
    return $this->sendMetafields($metafields, $value_type);
  }

  /**
   * Supprime un metafield
   *
   * @param array $metafields
   * @param string $value_type
   * @return boolean
   */
  public function deleteMetafield(array $metafields) {
    if ($this->validation($metafields, "delete")) {
      $id_entity = $metafields['id_entity'];
      $type = $metafields['type'];
      $id_metafield = $metafields['id_metafields'];

      switch ($type) {
        case 'blog':
          $this->path = "admin/api/" . self::$ApiVersion . "/blogs/{$id_entity}/metafields/{$id_metafield}.json";
          break;
        case 'article':
          if (empty($metafields['id_parent'])) {
            $this->has_error = true;
            $this->error_msg = "L'id_parent n'est pas défini pour l'article";
            throw new WbuShopifyException("L'id_parent n'est pas défini pour l'article");
          }
          $id_parent = $metafields['id_parent'];
          $this->path = "admin/api/" . self::$ApiVersion . "/blogs/{$id_parent}/articles/{$id_entity}/metafields/{$id_metafield}.json";
          break;
        case 'product':
          $this->path = "admin/api/" . self::$ApiVersion . "/products/{$id_entity}/metafields/{$id_metafield}.json";
          break;
        case 'page':
          $this->path = "admin/api/" . self::$ApiVersion . "/pages/{$id_entity}/metafields/{$id_metafield}.json";
          break;
        case 'collection':
        case 'custom_collection':
        case 'smart_collection':
          $this->path = "admin/api/" . self::$ApiVersion . "/collections/{$id_entity}/metafields/{$id_metafield}.json";
          break;
        case 'customer':
          $this->path = "admin/api/" . self::$ApiVersion . "/customers/{$id_entity}/metafields/{$id_metafield}.json";
          break;
        case 'order':
          $this->path = "admin/api/" . self::$ApiVersion . "/orders/{$id_entity}/metafields/{$id_metafield}.json";
          break;
        case 'draft_order':
          $this->path = "admin/api/" . self::$ApiVersion . "/draft_orders/{$id_entity}/metafields/{$id_metafield}.json";
          break;

        case 'variant':
          $this->path = "admin/api/" . self::$ApiVersion . "/variants/{$id_entity}/metafields/{$id_metafield}.json";
          break;
        default:
          $this->has_error = true;
          $this->error_msg = "Le type '{$type}' n'est pas encore pris en charge pour la suppression des metafields";
          throw new WbuShopifyException("Le type '{$type}' n'est pas encore pris en charge pour la suppression des metafields");
      }
      return $this->DeleteDatas();
    }
    else {
      $this->has_error = true;
      $this->error_msg = "Erreur lors de la suppression du metafield";
      throw new WbuShopifyException("Erreur lors de la suppression du metafield");
    }
    return false;
  }

  /**
   *
   * @param array $metafields
   * @param string $value_type
   */
  protected function sendMetafields($metafields, $value_type, $namespace = null) {
    // Ce type represente le type de données utilisé par Shopify.
    $metafields['type'] = $value_type;
    $this->validTypesAndDatas($metafields);
    $data = [];
    $data['metafield'] = [
      'namespace' => $namespace ? $namespace : $this->namespace,
      'key' => $metafields['key'],
      'value' => $metafields['value'],
      'type' => $metafields['type']
    ];
    $result = $this->PostDatas(json_encode($data));
    if ($this->default_ressource) {
      return $result;
    }
    // les resultats provenants de shopify sont uniquement du json.
    try {
      $result = json_decode($result, true);
    }
    catch (\Exception $e) {
      $this->has_error = true;
      $this->error_msg = ' Format de resultat non valide ';
      return $result;
    }
    $this->ValidResult($result);
    return $result;
  }
}
