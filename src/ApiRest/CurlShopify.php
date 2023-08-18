<?php

namespace Stephane888\WbuShopify\ApiRest;

use Stephane888\Debug\debugLog;

/**
 *
 * @author stephane
 *        
 */
class CurlShopify
{
  public $key_api = null;
  protected $last_response_headers = null;
  public $path = "";
  public $showHeader = 0;
  private $api_full_url = null;
  private $http_code = null;
  private $api_url;
  /**
   * Raw datas
   *
   * @var mixed
   */
  private $result;

  /**
   *
   * @var mixed
   */
  private $rawArg;

  function __construct($configs)
  {
    if (!empty($configs['api_key']) && !empty($configs['shop_domain']) && !empty($configs['secret'])) {
      $this->api_key = $configs['api_key'];
      $this->shop_domain = trim($configs['shop_domain'], "/");
      $this->secret = $configs['secret'];
    } else {
      $this->buildError("Configuration non valide, vous definir: 'api_key','shop_domain','secret','webhook_key' ", 401, []);
    }
  }

  /**
   *
   * @param
   *        $arg
   * @return mixed
   */
  protected function PostDatas($arg)
  {
    $this->rawArg = $arg;
    $this->api_full_url = 'https://' . $this->shop_domain . '/' . $this->path;
    $headers = array(
      "Content-Type: application/json; charset=utf-8",
      'Expect:'
    );

    $curl = curl_init($this->api_full_url);
    // curl_setopt($curl, CURLOPT_HEADER, 1);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_POST, 1);
    curl_setopt($curl, CURLOPT_POSTFIELDS, $arg);
    curl_setopt($curl, CURLOPT_USERPWD, $this->api_key . ':' . $this->secret);
    $this->result = curl_exec($curl);
    $this->http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return $this->result;
  }

  /**
   */
  public function GetDatas()
  {
    // $url=$this->db_api->protocole.'://'.$this->db_api->ndd.'/'.$this->path;
    $this->api_full_url = 'https://' . $this->shop_domain . '/' . $this->path;
    $headers = array(
      "Accept: application/json",
      "Content-Type: application/json"
    );
    // echo '<pre> URL : <br>'; var_dump($url); echo '</pre>';
    // ///////
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HEADER, $this->showHeader);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
    curl_setopt($ch, CURLOPT_URL, $this->api_full_url);
    // curl_setopt($ch, CURLOPT_POST, 1);
    // curl_setopt($ch, CURLOPT_POSTFIELDS, $args);
    curl_setopt($ch, CURLOPT_USERPWD, $this->api_key . ':' . $this->secret); // API
    // KEY
    $this->result = curl_exec($ch);
    $this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($this->http_code < 200 || $this->http_code > 299) {
      throw new \Exception('bad request response');
    }
    curl_close($ch);
    return $this->result;
  }

  public function get()
  {
    return $this->GetDatas();
  }

  /**
   *
   * @param string $arg
   * @return mixed
   */
  public function PutDatas($arg)
  {
    $this->rawArg = $arg;
    $this->api_full_url = 'https://' . $this->shop_domain . '/' . $this->path;
    $headers = array(
      "Content-Type: application/json; charset=utf-8",
      'Expect:'
    );

    $ch = curl_init();
    // curl_setopt($ch, CURLOPT_HEADER,$this->showHeader);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
    curl_setopt($ch, CURLOPT_POSTFIELDS, $arg);
    curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
    curl_setopt($ch, CURLOPT_URL, $this->api_full_url);
    curl_setopt($ch, CURLOPT_USERPWD, $this->api_key . ':' . $this->secret); // API
    // KEY
    $this->result = curl_exec($ch);
    $this->http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    return $this->result;
  }

  /**
   */
  public function DeleteDatas()
  {
    $this->api_full_url = 'https://' . $this->shop_domain . $this->path;
    $headers = array(
      "Content-Type: application/json; charset=utf-8",
      'Expect:'
    );
    $curl = curl_init($this->api_full_url);
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

    $this->result = curl_exec($curl);
    $this->http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);
    return $this->result;
  }

  public function get_http_code()
  {
    return $this->http_code;
  }

  /**
   * Retourne le resultat brute de la derniere requete.
   *
   * @return mixed
   */
  public function getRawBody()
  {
    return $this->result;
  }

  /**
   * Retourne l'url complete de la requete.
   */
  public function getFullUrl()
  {
    return $this->api_full_url;
  }

  /**
   *
   * @return mixed
   */
  public function getRawArg()
  {
    return $this->rawArg;
  }

  /**
   * Getionnaire d'erreur de logique.
   *
   * @param string $title
   * @param int $code
   * @param string|array $error
   */
  public function buildError($title, $code, $error)
  {
    $filename = 'CurlShopify_debug_' . date('m-Y');
    $data = [
      'title' => $title,
      'code' => $code,
      'error' => $error
    ];
    debugLog::saveLogs($data, $filename, 'logs');
    $msg = rawurlencode($title);
    header('HTTP/1.1 ' . $code . " " . $msg);
    // $error = json_encod
    die('BGQB###' . json_encode($error) . 'ENDQB###');
  }
}
