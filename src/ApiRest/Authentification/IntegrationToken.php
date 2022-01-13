<?php

namespace Stephane888\WbuShopify\ApiRest\Authentification;

use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\TransferStats;
use Stephane888\WbuShopify\Exception\WbuShopifyException;


/**
 * Ce fichier se charge de gerer l'authentification via le token.
 *
 * @author stephane
 *
 */
class IntegrationToken
{

	public $requestEndPoint;

	private $curl = null;

	private $payLoad = null;

	private $headers = [ ];

	private $accessToken;

	/**
	 *
	 * @param array $configs
	 */
	function __construct(array $configs = [ ])
	{
		if(! empty($configs))
			$this->setConfigs($configs);
	}

	/**
	 *
	 * @param array $configs
	 * @throws WbuShopifyException
	 */
	public function setConfigs(array $configs = [ ])
	{
		if(! empty($configs['domaine'])) {
			$this->setHost($configs['domaine']);
		}
		else {
			throw new WbuShopifyException(' Hote shopify non definit ');
		}
		if(! empty($configs['token'])) {
			$this->accessToken = $configs['token'];
		}
		else {
			throw new WbuShopifyException(' Token non definit ');
		}
		$this->buildHeader();
		// On doit adopter l'approche definit au niveau de : https://docs.guzzlephp.org/en/6.5/quickstart.html?highlight=file.
		$this->curl = new \GuzzleHttp\Client([
				'base_uri' => $this->getUrl()
			// 'headers' => $this->buildHeader()
		]);
	}

	/**
	 * Methode curl GET
	 *
	 * @return \Psr\Http\Message\ResponseInterface
	 */
	protected function get()
	{
		return $this->requeteExecute('GET');
	}

	/**
	 * -
	 */
	protected function GetDatas()
	{
		return $this->get();
	}

	/**
	 *
	 * @param string $data
	 * @return string|string[]|mixed[]|\Psr\Http\Message\RequestInterface[]
	 */
	protected function post(string $data)
	{
		return $this->requeteExecute('POST', $data);
	}

	/**
	 *
	 * @param String $data
	 * @return string|string[]|mixed[]|\Psr\Http\Message\RequestInterface[]
	 */
	protected function PostDatas($data)
	{
		return $this->post($data);
	}

	/**
	 *
	 * @param String $methode
	 * @param string $data
	 * @return string|string[]|mixed[]|\Psr\Http\Message\RequestInterface[]
	 */
	private function requeteExecute(String $methode, string $data = null)
	{
		try {
			if(! $this->curl || ! $this->requestEndPoint) {
				throw new WbuShopifyException('Token non definit ou Hote shopify ou EndPoint non definit');
			}
			if($data) {
				$this->payLoad = $data;
				$result = $this->curl->request($methode, trim($this->requestEndPoint, "/"), [
						'headers' => $this->headers,
						'body' => $data,
						'on_stats' => function (TransferStats $stats){
							$this->lastRequestUrl = $stats->getEffectiveUri();
							$this->transferTime = $stats->getTransferTime();
							$this->handlerStats = $stats->getHandlerStats();

							// You must check if a response was received before using the
							// response object.
							if($stats->hasResponse()) {
								// echo $stats->getResponse()->getStatusCode();
							}
							else {
								// Error data is handler specific. You will need to know what
								// type of error data your handler uses before using this
								// value.
								// var_dump($stats->getHandlerErrorData());
							}
						}
				]);
			}
			else {
				$result = $this->curl->request($methode, trim($this->requestEndPoint, "/"), [
						'headers' => $this->headers,
						'on_stats' => function (TransferStats $stats){
							$this->lastRequestUrl = $stats->getEffectiveUri();
							$this->transferTime = $stats->getTransferTime();
							$this->handlerStats = $stats->getHandlerStats();
						}
				]);
			}
			return $this->traitementRequest($result);
		} catch ( RequestException $e ) {
			// On doit utilier RequestException
			return $this->buildError($e);
		}
	}

	protected function traitementRequest(ResponseInterface $result)
	{
		return $result->getBody()->getContents();
	}

	function buildError(RequestException $e)
	{
		$body = $e->getResponse()->getBody()->getContents();
		$errors = [
				'code' => $e->getCode(),
				'message' => $e->getMessage(),
				'vue par le serveur distant' => $e->hasResponse() ? 'Oui' : 'Non',
				'request' => $e->getRequest(),
				'response' => [
						'body' => json_decode($body),
						'bodyRaw' => $body,
						'Headers' => $e->getResponse()->getHeaders(),
						'Code' => $e->getResponse()->getStatusCode(),
						'title' => $e->getResponse()->getReasonPhrase()
				],
				'payload' => json_decode($this->payLoad),
				'lastRequestUrl' => $this->lastRequestUrl->getHost() . '/' . $this->lastRequestUrl->getQuery(),
				'transferTime' => $this->transferTime,
				'handlerStats' => $this->handlerStats,
				'token' => $this->accessToken,
				'headers' => $this->headers
		];
		$msg = $e->getMessage();
		$msg = explode("\n", $msg);
		return $errors;
		throw new WbuShopifyException($msg[0], $e->getResponse()->getStatusCode());
	}

	/**
	 * Get base url
	 *
	 * @return string
	 */
	protected function getUrl()
	{
		$this->url = "https://" . $this->domain . '/';
		return $this->url;
	}

	private function buildHeader()
	{
		$this->addHeader('Accept', 'application/json');
		$this->addHeader('Content-Type', 'application/json');
		$this->addHeader('Output-Format', 'Output-Format');
		return $this->headers;
	}

	public function addHeader($key, $value)
	{
		$this->headers[$key] = $value;
	}

	public function authentificationXShopify()
	{
		$this->addHeader("X-Shopify-Access-Token", $this->accessToken);
	}

	public function authentificationBearer()
	{
		$this->addHeader("Authorization", "Bearer " . $this->accessToken);
	}

	public function setHost($domain)
	{
		$this->domain = $domain;
	}

	/**
	 * Permet de determiner s'il ya une erreur;
	 */
	protected function ValidResult($result)
	{
		if(! empty($result['errors'])) {
			$this->has_error = true;
			$this->error_msg = $this->getErrorString($result['errors']);
		}
	}

	private function getErrorString($errors)
	{
		if(\is_array($errors)) {
			$errors = reset($errors);
			if(\is_array($errors)) {
				$errors = reset($errors);
				$this->getErrorString($errors);
			}
			return $errors;
		}
		else {
			return $errors;
		}
	}


}
