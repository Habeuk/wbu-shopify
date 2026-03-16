<?php

namespace Stephane888\WbuShopify\ApiRest\Metafields;

use Stephane888\WbuShopify\Exception\WbuShopifyException;

trait MetafieldsValidations {

  /**
   * Validation de la structure et des clées utilisés.
   *
   * @param array $metafields
   * @return boolean
   */
  protected function validation(array $metafields, $action = "save") {
    $this->has_error = true;
    $error_messages = [
      'key' => 'La clée n\'est pas definie',
      'type' => 'Le type n\'est pas definie', // Le type doit etre remplacer
                                               // par entity_name.
      'id_entity' => 'L\'id_entity n\'est pas definie',
      'value' => 'La valeur n\'est pas definie',
      'id_metafields' => 'L\'id du metafield à supprimer n\'est pas definie'
    ];

    // Valider les champs communs
    $common_fields = [
      'type',
      'id_entity'
    ];
    foreach ($common_fields as $field) {
      if (empty($metafields[$field])) {
        $this->error_msg = $error_messages[$field];
        throw new WbuShopifyException($error_messages[$field]);
      }
    }
    switch ($action) {
      case "save":
        if (!isset($metafields['value'])) {
          $this->error_msg = $error_messages['value'];
          throw new WbuShopifyException($error_messages['value']);
        }
        if (empty($metafields['key'])) {
          $this->error_msg = $error_messages['key'];
          throw new WbuShopifyException($error_messages['key']);
        }
        break;
      case "delete":
        if (empty($metafields['id_metafields'])) {
          $this->error_msg = $error_messages['id_metafields'];
          throw new WbuShopifyException($error_messages['id_metafields']);
        }
        break;
      default:
        $this->error_msg = 'Type d\'opération invalide';
        throw new WbuShopifyException('Type d\'opération invalide');
    }
    $this->has_error = false;
    return true;
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
   * Valid le type et les données à envoyer.
   *
   * @see https://shopify.dev/docs/apps/build/metafields/list-of-data-types
   * @param array $metafields
   */
  protected function validTypesAndDatas(array &$metafields) {
    // Vérifier que le type existe
    if (!isset($metafields['type'])) {
      throw new WbuShopifyException("Le type de metafield n'est pas défini");
    }
    $type = $metafields['type'];
    switch ($type) {
      case 'number_integer':
      case 'integer': // @deprecated
        $this->handleIntegerType($metafields);
        break;

      case 'json':
      case 'json_string': // @deprecated
        $this->handleJsonType($metafields);
        break;

      case 'single_line_text_field':
      case 'string': // @deprecated
        $this->handleStringType($metafields);
        break;

      case 'number_decimal':
      case 'float': // @deprecated
      case 'double': // @deprecated
        $this->handleDecimalType($metafields);
        break;

      case 'boolean':
      case 'bool': // @deprecated
        $this->handleBooleanType($metafields);
        break;

      case 'date':
        $this->handleDateType($metafields);
        break;

      case 'date_time':
        $this->handleDateTimeType($metafields);
        break;

      case 'url':
        $this->handleUrlType($metafields);
        break;

      case 'color':
      case 'color_rgb': // @deprecated
        $this->handleColorType($metafields);
        break;

      case 'weight':
      case 'volume':
      case 'dimension':
        $this->handleMeasurementType($metafields, $type);
        break;

      case 'multi_line_text_field':
      case 'rich_text_field':
        // Pas de traitement spécial nécessaire
        $this->logDeprecatedType($type, $type);
        break;

      default:
        // Type non reconnu mais on laisse passer avec un warning
        $this->logWarning("Type de metafield non standard: {$type}");
        break;
    }
  }

  /**
   * Gère le type entier
   */
  private function handleIntegerType(array &$metafields) {
    if ($metafields['type'] == 'integer') {
      $metafields['type'] = 'number_integer';
    }
    // Validation supplémentaire si nécessaire
    if (!is_numeric($metafields['value'])) {
      throw new WbuShopifyException("La valeur doit être numérique pour le type integer");
    }
    $metafields['value'] = (int) $metafields['value'];
  }

  /**
   * Gère le type JSON
   */
  private function handleJsonType(array &$metafields) {
    if ($metafields['type'] == 'json_string') {
      $metafields['type'] = 'json';
    }
    if (!is_array($metafields['value'])) {
      throw new WbuShopifyException("Le type de donnée doit être un array pour le type json");
    }
    $metafields['value'] = json_encode($metafields['value'], JSON_UNESCAPED_UNICODE);
    if (json_last_error() !== JSON_ERROR_NONE) {
      throw new WbuShopifyException("Erreur d'encodage JSON: " . json_last_error_msg());
    }
  }

  /**
   * Gère le type texte
   */
  private function handleStringType(array &$metafields) {
    if ($metafields['type'] == 'string') {
      $metafields['type'] = 'single_line_text_field';
    }
    // Conversion en string si nécessaire
    $metafields['value'] = $metafields['value'];
  }

  /**
   * Gère le type décimal
   */
  private function handleDecimalType(array &$metafields) {
    $oldType = $metafields['type'];
    if (in_array($oldType, [
      'float',
      'double'
    ])) {
      $metafields['type'] = 'number_decimal';
    }

    if (!is_numeric($metafields['value'])) {
      throw new WbuShopifyException("La valeur doit être numérique pour le type decimal");
    }
    $metafields['value'] = (float) $metafields['value'];
  }

  /**
   * Gère le type booléen
   */
  private function handleBooleanType(array &$metafields) {
    if ($metafields['type'] == 'bool') {
      $metafields['type'] = 'boolean';
    }

    // Conversion en booléen
    $metafields['value'] = filter_var($metafields['value'], FILTER_VALIDATE_BOOLEAN);
  }

  /**
   * Gère le type date
   */
  private function handleDateType(array &$metafields) {
    $timestamp = strtotime($metafields['value']);
    if ($timestamp === false) {
      throw new WbuShopifyException("Format de date invalide pour le type date");
    }
    $metafields['value'] = date('Y-m-d', $timestamp);
  }

  /**
   * Gère le type date_time
   */
  private function handleDateTimeType(array &$metafields) {
    $timestamp = strtotime($metafields['value']);
    if ($timestamp === false) {
      throw new WbuShopifyException("Format de date/heure invalide pour le type date_time");
    }
    $metafields['value'] = date('Y-m-d\TH:i:s\Z', $timestamp);
  }

  /**
   * Gère le type URL
   */
  private function handleUrlType(array &$metafields) {
    if (!filter_var($metafields['value'], FILTER_VALIDATE_URL)) {
      throw new WbuShopifyException("URL invalide pour le type url");
    }
  }

  /**
   * Gère le type couleur
   */
  private function handleColorType(array &$metafields) {
    if ($metafields['type'] == 'color_rgb') {
      $metafields['type'] = 'color';
    }

    if (!preg_match('/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/', $metafields['value'])) {
      throw new WbuShopifyException("Format de couleur invalide. Utilisez #RRGGBB ou #RGB");
    }
  }

  /**
   * Gère les types de mesure
   */
  private function handleMeasurementType(array &$metafields, string $type) {
    if (!is_numeric($metafields['value'])) {
      throw new WbuShopifyException("La valeur doit être numérique pour le type {$type}");
    }
    // Optionnel: valider l'unité si présente
    if (isset($metafields['unit'])) {
      $validUnits = $this->getValidUnitsForType($type);
      if (!in_array($metafields['unit'], $validUnits)) {
        throw new WbuShopifyException("Unité invalide pour {$type}. Unités valides: " . implode(', ', $validUnits));
      }
    }
  }

  /**
   * Retourne les unités valides pour un type de mesure
   */
  private function getValidUnitsForType(string $type): array {
    $units = [
      'weight' => [
        'kg',
        'g',
        'lb',
        'oz'
      ],
      'volume' => [
        'ml',
        'l',
        'cl',
        'fl_oz',
        'gal'
      ],
      'dimension' => [
        'cm',
        'm',
        'in',
        'ft',
        'mm'
      ]
    ];

    return $units[$type] ?? [];
  }
}

