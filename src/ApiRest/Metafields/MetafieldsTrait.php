<?php

namespace Stephane888\WbuShopify\ApiRest\Metafields;

use Stephane888\WbuShopify\Exception\WbuShopifyException;


/**
 * Ce trait doit etre ajouter dans une class sui etend la classe Wbu\ApiRest\Shopify
 *
 * @author stephane
 *
 */
trait MetafieldsTrait
{

	/**
	 * Permet de retourner la reponse brute.
	 *
	 * @var boolean
	 */
	public $default_ressource = false;

	public function LoadMetafiels()
	{
		return $this->get();
	}

	public function save(array $metafields)
	{
		$result = [ ];
		foreach ( $metafields as $metafield ) {
			$this->Validated($metafield);
			$result[] = $this->sendMetafields($metafield, $metafield['value_type'], $metafield['namespace']);
		}
		return $result;
	}

	protected function Validated($metafield)
	{
		if(! isset($metafield['namespace'])) {
			throw new WbuShopifyException("L'attribut 'namespace' non definit");
		}
		if(! isset($metafield['key'])) {
			throw new WbuShopifyException("L'attribut 'key' non definit");
		}
		if(! isset($metafield['value'])) {
			throw new WbuShopifyException("L'attribut 'value' non definit");
		}
		if(! isset($metafield['value_type'])) {
			throw new WbuShopifyException("L'attribut 'value_type' non definit");
		}
	}

	/**
	 *
	 * @param array $metafields
	 * @param string $value_type
	 */
	protected function sendMetafields($metafields, $value_type, $namespace = null)
	{
		if(is_array($metafields['value'])) {
			$metafields['value'] = json_encode($metafields['value']);
		}
		$data = [ ];
		$data['metafield'] = [
				'namespace' => $namespace ? $namespace : $this->namespace,
				'key' => $metafields['key'],
				'value' => $metafields['value'],
				'value_type' => $value_type
		];
		$result = $this->PostDatas(json_encode($data));
		if($this->default_ressource) {
			return $result;
		}
		$result = json_decode($result, true);
		$this->ValidResult($result);
		return $result;
	}


}