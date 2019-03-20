<?php

namespace Airgrow;

class Airgrow {
    public static $key;
    public static $tracker;
    public static $product;
    public static $authorized;

    function __construct(){
    }

    public static function setApiKey($key, $product){
        self::$key = $key;
	self::$product = $product;
	
        $authorization = self::post([
            "key" => self::$key,
	    "product" => self::$product,
            "action" => "login" 
        ], "https://tracker.airgrow.com/collect.php");

	print_r($authorization);

        self::$authorized = ($authorization["status"] == "success");

        if (self::$authorized){
        	self::$tracker = $authorization["tracker"];
	}
    }

    public static function event($name, $parameters = []){
        $data = [
            "product" => self::$product,
            "key" => self::$key,
            "action" => "custom",
            "id" => "api:" . $name
        ];

        foreach ($parameters as $key => $value) 
            $data[$key] = $value;
        
        return self::post($data);
    }

    private static function post($data, $url = NULL){
        $curl = curl_init();

        if ($url == NULL){
            if (!self::$authorized)
                return false;

            $url = "https://" . self::$tracker . "/event";
        }

        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $result = curl_exec($curl);

        curl_close($curl);

        return (array)json_decode("[" . $result . "]")[0];
    }   
};

?>
