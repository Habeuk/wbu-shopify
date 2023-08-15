<?php

namespace Stephane888\WbuShopify\ApiRest\Customers;

use Stephane888\WbuShopify\ApiRest\Shopify;
use Stephane888\WbuShopify\ApiRest\Metafields\MetafieldsTrait;

class Customers extends Shopify
{
    use MetafieldsTrait;

    function __construct($configs)
    {
        parent::__construct($configs);
    }

    /**
     * Permet de recuperer les customers.
     */
    public function getCustomers($path = null)
    {
        if (!$path)
            $this->path = 'admin/api/' . $this->api_version . '/customers.json';
        $datas = $this->GetDatas();
        return json_decode($datas, true);
    }


    /**
     * Permet de recuperer le customer.
     */
    public function getCustomer($customerid, $path = null)
    {
        if (!$path)
            $this->path = 'admin/api/' . $this->api_version . '/customers/' . $customerid . '.json';
        $datas = $this->GetDatas();
        return json_decode($datas, true);
    }


    /**
     * Permet de récupérer les commandes d'un client spécifique
     */
    public function getCustomerOrders($customerId)
    {
        $this->path = 'admin/api/' . $this->api_version . '/customers/' . $customerId . '/orders.json?status=any&limit=250';
        $datas = $this->GetDatas();
        return json_decode($datas, true);
    }

    /**
     *
     * @param integer $id_blog
     * @return mixed
     */
    public function getMetafields($customerid)
    {
        $this->path = 'admin/api/' . $this->api_version . '/products/' . $customerid . '/metafields.json';
        return $this->LoadMetafiels();
    }

    /**
     * Create a new product
     * @param array $product
     */
    public function addCustomer($customer)
    {
        $data = [
            "customer" => $customer
        ];
        $this->path = 'admin/api/' . $this->api_version . '/customers.json';
        $result = json_decode($this->PostDatas(json_encode($data)), true);

        return $result;
    }
}
