<?php

namespace Stephane888\WbuShopify\ApiRest;

use Stephane888\Debug\debugLog;
use Stephane888\WbuShopify\Exception\WbuShopifyException;

/**
 *
 * @author stephane
 *        
 */
class CurlShopify {
  public static $debug = false;
  public $key_api = null;
  protected $last_response_headers = null;
  public $path = "";
  public $showHeader = 0;
  public $api_full_url = null;
  protected $http_code = null;
  protected $has_token;
  
  function __construct($configs) {
    if (!empty($configs['api_key']) && !empty($configs['shop_domain']) && !empty($configs['secret'])) {
      $this->api_key = $configs['api_key'];
      $this->shop_domain = $configs['shop_domain'];
      $this->secret = $configs['secret'];
    }
    else {
      throw new WbuShopifyException("Configuration non valide, vous definir: 'api_key','shop_domain','secret','webhook_key' ", 401);
    }
    
    if (!empty($configs['token'])) {
      $this->has_token = true;
      $this->token = $configs['token'];
    }
  }
  
  /**
   *
   * @param
   *        $arg
   * @return mixed
   */
  protected function PostDatas($arg) {
    
    // $api_url =
    // $this->db_api->protocole.'://'.$this->db_api->ndd.'/'.$this->path;
    $api_url = 'https://' . $this->shop_domain . '/' . $this->path;
    $headers = array(
      "Content-Type: application/json; charset=utf-8",
      'Expect:'
    );
    
    // add token header if it is defined
    if ($this->has_token) {
      $headers[] = "X-Shopify-Access-Token: " . $this->token;
    }
    
    $curl = curl_init($api_url);
    // curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $arg);
    curl_setopt($curl, CURLOPT_USERPWD, $this->api_key . ':' . $this->secret); // API
                                                                               // KEY
    $result = curl_exec($curl); // 返回结果
    $this->http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    // echo '<pre>'; var_dump(json_decode($result)); echo '</pre>';
    return $result;
  }
  
  /**
   */
  public function GetDatas() {
    // $url=$this->db_api->protocole.'://'.$this->db_api->ndd.'/'.$this->path;
    $url = 'https://' . $this->shop_domain . '/' . $this->path;
    $headers = array(
      "Accept: application/json",
      "Content-Type: application/json"
    );
    
    // add token header if it is defined
    if ($this->has_token) {
      $headers[] = "X-Shopify-Access-Token: " . $this->token;
    }
    // echo '<pre> URL : <br>'; var_dump($url); echo '</pre>';
    // ///////
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, $this->showHeader);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    // curl_setopt($ch, CURLOPT_POST, 1);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
    curl_setopt($ch, CURLOPT_USERPWD, $this->api_key . ':' . $this->secret); // API
                                                                             // KEY
    $result = curl_exec($ch);
    $this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $result;
  }
  
  public function get() {
    return $this->GetDatas();
  }
  
  /**
   *
   * @param string $arg
   * @return mixed
   */
  public function PutDatas($arg) {
    $url = 'https://' . $this->shop_domain . '/' . $this->path;
    $headers = array(
      "Content-Type: application/json; charset=utf-8",
      'Expect:'
    );
    
    // add token header if it is defined
    if ($this->has_token) {
      $headers[] = "X-Shopify-Access-Token: " . $this->token;
    }
    
    $ch = curl_init();
    // curl_setopt($ch, CURLOPT_HEADER,$this->showHeader);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $arg);
    curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERPWD, $this->api_key . ':' . $this->secret); // API
                                                                             // KEY
    $result = curl_exec($ch);
    $this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $result;
  }
  
  /**
   */
  public function DeleteDatas() {
    $api_url = $this->api_full_url = 'https://' . $this->shop_domain . $this->path;
    $headers = array(
      "Content-Type: application/json; charset=utf-8",
      'Expect:'
    );
    
    // add token header if it is defined
    if ($this->has_token) {
      $headers[] = "X-Shopify-Access-Token: " . $this->token;
    }
    $curl = curl_init($api_url);
    // curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    // curl_setopt($curl, CURLOPT_POST, 1);
    // curl_setopt($curl, CURLOPT_POSTFIELDS, $arg);
    curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
    curl_setopt($curl, CURLOPT_USERPWD, $this->api_key . ':' . $this->secret); // API
                                                                               // KEY
    
    $result = curl_exec($curl);
    $this->http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return $result;
  }
  
  public function get_http_code() {
    return $this->http_code;
  }
  
  /**
   * Getionnaire d'erreur de logique.
   *
   * @param string $title
   * @param int $code
   * @param string|array $error
   */
  public function buildError($title, $code, $error) {
    $filename = 'CurlShopify_debug_' . date('m-Y');
    $data = [
      'title' => $title,
      'code' => $code,
      'error' => $error
    ];
    if (self::$debug)
      debugLog::saveLogs($data, $filename, 'logs');
    $msg = rawurlencode($title);
    header('HTTP/1.1 ' . $code . " " . $msg);
    // $error = json_encod
    $debug = new WbuShopifyException($title, $code, $error);
    $debug->setErrors([
      $error
    ]);
    throw $debug;
  }
  
}