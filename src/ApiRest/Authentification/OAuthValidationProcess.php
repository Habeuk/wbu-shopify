<?php

namespace Stephane888\WbuShopify\ApiRest\Authentification;

use Symfony\Component\HttpFoundation\Request;
use Stephane888\WbuShopify\Exception\WbuShopifyException;
use Shopify\Auth\OAuth;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;

/**
 * --
 *
 * @author stephane
 *        
 */
class OAuthValidationProcess {
  protected $http_code = 200;
  
  /**
   * Permet de valider les requetes shopify.
   * si le paramettre : state={nonce} est definie, on doit egalement le valider
   *
   * @see https://shopify.dev/apps/auth/oauth/getting-started#process-the-new-string-through-a-hash-function
   */
  public function ValidationRequest(Request $Request) {
    return true;
  }
  
  /**
   * Permet de demande les autorisations.
   *
   * @param Request $Request
   */
  public function AskAuthorization(Request $Request, array $grantOptions = []) {
    $url = $this->generateRedirectUrl($Request);
    $this->Redirect($url);
  }
  
  /**
   * Recupere to token d'access sur shopify.
   *
   * @param Request $Request
   * @param array $confs
   *        example return :
   *        {"access_token":"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","scope":"write_products"}
   */
  public function GetTokenAccess(Request $Request) {
    if (empty($this->configs)) {
      throw new WbuShopifyException('Configuration non definit');
    }
    $params = $Request->query->all();
    $curl = new \GuzzleHttp\Client([
      'base_uri' => "https://" . $params['shop']
    ]);
    $result = $curl->request('POST', '/admin/oauth/access_token', [
      'query' => [
        'client_id' => $this->configs['client_id'],
        'client_secret' => $this->configs['client_secret'],
        'code' => $params['code']
      ]
    ]);
    return $this->traitementRequest($result);
  }
  
  /**
   * --
   */
  private function generateRedirectUrl(Request $Request) {
    if (empty($this->configs)) {
      throw new WbuShopifyException('Configuration non definit');
    }
    $params = $Request->query->all();
    $grantOptions = '';
    $sanitizedShop = $params['shop'];
    $query = [
      'client_id' => $this->configs['client_id'],
      'scope' => $this->retrieveScope($this->configs['grant_options']),
      'redirect_uri' => $this->configs['redirect_uri'],
      'state' => 'kksa55845795',
      'grant_options[]' => $grantOptions
    ];
    return "https://{$sanitizedShop}/admin/oauth/authorize?" . http_build_query($query);
  }
  
  /**
   * --
   *
   * @param array $grantOptions
   * @return string
   */
  private function retrieveScope(array $grantOptions) {
    $scope = '';
    foreach ($grantOptions as $value) {
      if ($value)
        $scope .= $value . ',';
    }
    $scope = trim($scope, ',');
    return $scope;
  }
  
  private function Redirect($url, $permanent = false) {
    header('Location: ' . $url, true, $permanent ? 301 : 302);
    exit();
  }
  
  protected function traitementRequest(ResponseInterface $result) {
    return $result->getBody()->getContents();
  }
  
  /**
   * --
   */
  public function get_http_code() {
    return $this->http_code;
  }
  
  function buildError(RequestException $e) {
    $body = $e->getResponse()->getBody()->getContents();
    $this->http_code = $e->getResponse()->getStatusCode();
    $errors = [
      'code' => $e->getCode(),
      'message' => $e->getMessage(),
      'vue par le serveur distant' => $e->hasResponse() ? 'Oui' : 'Non',
      'request' => $e->getRequest(),
      'response' => [
        'body' => json_decode($body),
        'bodyRaw' => $body,
        'Headers' => $e->getResponse()->getHeaders(),
        'Code' => $this->http_code,
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
    $dbg = new WbuShopifyException($msg, $e->getResponse()->getStatusCode());
    $dbg->setErrors($errors);
    throw $dbg;
  }
  
}