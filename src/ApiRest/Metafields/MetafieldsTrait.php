<?php

namespace Stephane888\WbuShopify\ApiRest\Metafields;

use Stephane888\WbuShopify\Exception\WbuShopifyException;

/**
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
   */
  public $default_ressource = false;
  
  public function LoadMetafiels() {
    return $this->get();
  }
  
  /**
   * Utiliser principalement par IntegrationToken et permet d'enregistrer
   * plusisieurs metafields.
   *
   * @param array $metafields
   * @return []
   */
  public function save(array $metafields) {
    $result = [];
    foreach ($metafields as $metafield) {
      $this->Validated($metafield);
      $result[] = $this->sendMetafield($metafield, $metafield['value_type'], $metafield['namespace']);
    }
    return $result;
  }
  
  /**
   * Permet d'enregistrer un metafield.
   *
   * @param array $metafield
   * @param string $value_type
   * @param string $endPoint
   * @return array|mixed
   */
  public function saveMetafields(array $metafield, $value_type = "string", $endPoint = null) {
    if ($this->validation($metafield)) {
      // On surcharge le type de meteafield.
      if (!empty($metafield['type_metafield'])) {
        $value_type = $metafield['type_metafield'];
      }
      // On encode les valeurs dans le cas de json_string.
      if ($value_type == "json_string") {
        $metafield['value'] = json_encode($metafield['value']);
      }
      //
      if ($endPoint) {
        $this->path = $endPoint;
        return $this->sendMetafield($metafield, $value_type);
      }
      
      // on essaie de determiner le endpoint partir des données envoyées.(
      // utiliser par nutribe ).
      $id_entity = $metafield['id_entity'];
      if ($metafield['type'] == 'blog') {
        $this->path = 'admin/api/' . $this->api_version . '/blogs/' . $id_entity . '/metafields.json';
        return $this->sendMetafield($metafield, $value_type);
      }
      if ($metafield['type'] == 'article') {
        if (empty($metafield['id_parent'])) {
          $this->has_error = true;
          $this->error_msg = 'L\'id_parent n\'est pas definie';
        }
        else {
          $id_parent = $metafield['id_parent'];
          $this->path = 'admin/api/' . $this->api_version . '/blogs/' . $id_parent . '/articles/' . $id_entity . '/metafields.json';
          return $this->sendMetafield($metafield, $value_type);
        }
      }
      if ($metafield['type'] == 'product') {
        $this->path = 'admin/api/' . $this->api_version . '/products/' . $id_entity . '/metafields.json';
        return $this->sendMetafield($metafield, $value_type);
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
  
  protected function validation($metafield) {
    $this->has_error = true;
    if (empty($metafield['key'])) {
      $this->error_msg = ('La clée n\'est pas definie');
      return false;
    }
    if (!isset($metafield['value'])) {
      $this->error_msg = ('La valeur n\'est pas definie');
      return false;
    }
    if (empty($metafield['type'])) {
      $this->error_msg = ('Le type n\'est pas definie');
      return false;
    }
    if (empty($metafield['id_entity'])) {
      $this->error_msg = ('L\'id_entity n\'est pas definie');
      return false;
    }
    $this->has_error = false;
    return true;
  }
  
  /**
   *
   * @param array $metafield
   * @param string $value_type
   */
  protected function sendMetafield($metafield, $value_type, $namespace = null) {
    if (is_array($metafield['value'])) {
      $metafield['value'] = json_encode($metafield['value']);
    }
    $data = [];
    $data['metafield'] = [
      'namespace' => $namespace ? $namespace : $this->namespace,
      'key' => $metafield['key'],
      'value' => $metafield['value'],
      'value_type' => $value_type
    ];
    $result = $this->PostDatas(json_encode($data));
    if ($this->default_ressource) {
      return $result;
    }
    if ($this->get_http_code() == 200) {
      $result = json_decode($result, true);
      $this->ValidResult($result);
      return $result;
    }
    else {
      return $result;
    }
  }
  
}