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
  
  public function save(array $metafields) {
    $result = [];
    foreach ($metafields as $metafield) {
      $this->Validated($metafield);
      $result[] = $this->sendMetafields($metafield, $metafield['value_type'], $metafield['namespace']);
    }
    return $result;
  }
  
  /**
   * Permet de traiter les metafields avant sauvegarde.
   * Cest plus un helper.
   *
   * @param array $metafields
   * @param string $value_type
   * @return mixed
   */
  public function saveMetafields(array $metafields, $value_type = "single_line_text_field") {
    if ($this->validation($metafields)) {
      /**
       * On surcharge le type.
       */
      if (!empty($metafields['type_metafield'])) {
        $value_type = $metafields['type_metafield'];
      }
      // if ($value_type == "json_string") {
      // $metafields['value'] = json_encode($metafields['value']);
      // }
      $id_entity = $metafields['id_entity'];
      if ($metafields['type'] == 'blog') {
        $this->path = 'admin/api/' . $this->api_version . '/blogs/' . $id_entity . '/metafields.json';
        return $this->sendMetafields($metafields, $value_type);
      }
      if ($metafields['type'] == 'article') {
        if (empty($metafields['id_parent'])) {
          $this->has_error = true;
          $this->error_msg = 'L\'id_parent n\'est pas definie';
        }
        else {
          $id_parent = $metafields['id_parent'];
          $this->path = 'admin/api/' . $this->api_version . '/blogs/' . $id_parent . '/articles/' . $id_entity . '/metafields.json';
          return $this->sendMetafields($metafields, $value_type);
        }
      }
      if ($metafields['type'] == 'product') {
        $this->path = 'admin/api/' . $this->api_version . '/products/' . $id_entity . '/metafields.json';
        return $this->sendMetafields($metafields, $value_type);
      }
      if ($metafields['type'] == 'page') {
        $this->path = 'admin/api/' . $this->api_version . '/pages/' . $id_entity . '/metafields.json';
        return $this->sendMetafields($metafields, $value_type);
      }
      $this->has_error = true;
      $this->error_msg = 'Le type de metafileds n\'est pas encore pris en charge';
    }
  }
  
  protected function Validated($metafield) {
    if (empty($metafield['namespace'])) {
      throw new WbuShopifyException("L'attribut 'namespace' non definit");
    }
    if (!isset($metafield['key'])) {
      throw new WbuShopifyException("L'attribut 'key' non definit");
    }
    if (!isset($metafield['value'])) {
      throw new WbuShopifyException("L'attribut 'value' non definit");
    }
    if (!isset($metafield['value_type'])) {
      throw new WbuShopifyException("L'attribut 'value_type' non definit");
    }
  }
  
  /**
   * Validation de la structure.
   *
   * @param array $metafields
   * @return boolean
   */
  protected function validation(array &$metafields) {
    $this->has_error = true;
    if (empty($metafields['key'])) {
      $this->error_msg = ('La clée n\'est pas definie');
      return false;
    }
    if (!isset($metafields['value'])) {
      $this->error_msg = ('La valeur n\'est pas definie');
      return false;
    }
    if (empty($metafields['type'])) {
      $this->error_msg = ('Le type n\'est pas definie');
      return false;
    }
    if (empty($metafields['id_entity'])) {
      $this->error_msg = ('L\'id_entity n\'est pas definie');
      return false;
    }
    $this->has_error = false;
    return true;
  }
  
  /**
   * Valid le type et les données envoyées.
   *
   * @see https://shopify.dev/docs/apps/custom-data/metafields/types
   * @param array $metafields
   */
  protected function validTypesAndDatas(array &$metafields) {
    $type = $metafields['type'];
    switch ($type) {
      case 'number_integer':
      case 'integer': // @depreciate type
        if ($type == 'integer') {
          \Stephane888\Debug\debugLog::saveLogs($metafields, 'validTypes', 'logs', "Le type de metafield n'est pas valide 'integer'", "Le type de metafield n'est pas valide 'integer'");
          $metafields['type'] = 'number_integer';
        }
        if (!is_numeric($metafields['value']))
          \Stephane888\Debug\debugLog::saveLogs($metafields, 'validTypes', 'logs', "La valeur n'est pas valide 'value type: is_numeric'", "La valeur n'est pas valide 'value type: is_numeric'");
        break;
      case 'json':
      case 'json_string': // @depreciate type
        if ($type == 'json_string') {
          \Stephane888\Debug\debugLog::saveLogs($metafields, 'validTypes', 'logs', "Le type de metafield n'est pas valide 'json_string'", "Le type de metafield n'est pas valide 'json_string'");
          $metafields['type'] = 'json';
        }
        if (!is_array($metafields['value']))
          throw new WbuShopifyException("Le type de donnée doit etre un array ");
        $metafields['value'] = json_encode($metafields['value']);
        break;
      case 'single_line_text_field':
      case 'string': // @depreciate type
        if ($type == 'string') {
          \Stephane888\Debug\debugLog::saveLogs($metafields, 'validTypes', 'logs', "Le type de metafield n'est pas valide 'string'", "Le type de metafield n'est pas valide 'string'");
          $metafields['type'] = 'single_line_text_field';
        }
        break;
      default:
        \Stephane888\Debug\debugLog::saveLogs($metafields, 'validTypes', 'logs', "Le type de metafield n'est pas traité '$type'", "Le type de metafield n'est pas traité '$type'");
        break;
    }
  }
  
  /**
   *
   * @param array $metafields
   * @param string $value_type
   */
  protected function sendMetafields($metafields, $value_type, $namespace = null) {
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
