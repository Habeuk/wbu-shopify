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
  
  public function saveMetafields($metafields, $value_type = "string") {
    if ($this->validation($metafields)) {
      /**
       * On surcharge le type.
       */
      if (!empty($metafields['type_metafield'])) {
        $value_type = $metafields['type_metafield'];
      }
      if ($value_type == "json_string") {
        $metafields['value'] = json_encode($metafields['value']);
      }
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
  
  protected function validation($metafields) {
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
   *
   * @param array $metafields
   * @param string $value_type
   */
  protected function sendMetafields($metafields, $value_type, $namespace = null) {
    if (is_array($metafields['value'])) {
      $metafields['value'] = json_encode($metafields['value']);
    }
    $data = [];
    // Conversion des données suivant la version 2023/01
    switch ($value_type) {
      case 'json_string':
        $value_type = 'json_string'; // 'json';
        break;
      case 'integer':
        $value_type = 'number_integer';
        break;
      case 'string':
        $value_type = 'single_line_text_field';
        break;
    }
    $data['metafield'] = [
      'namespace' => $namespace ? $namespace : $this->namespace,
      'key' => $metafields['key'],
      'value' => $metafields['value'],
      'type' => $value_type
    ];
    $result = $this->PostDatas(json_encode($data));
    if ($this->default_ressource) {
      return $result;
    }
    // les resutats provenants de shopify sont uniquement du json.
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
