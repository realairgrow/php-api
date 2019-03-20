<?php

namespace Airgrow;

class Airgrow {
    function __construct(){
    }

    function setApiKey($key){
        $this->key = $key;
	
        $authorization = $this->post([
            "key" => $this->key,
            "action" => "login" 
        ], "https://tracker.airgrow.com/collect.php");

        $this->authorized = ($authorization["status"] == "success");

        if ($this->authorized){
        	$this->tracker = $authorization["tracker"];
		$this->product = $authorization["product"];
	}
 
    }

    function event($name, $parameters = []){
        $data = [
            "product" => $this->product,
            "key" => $this->key,
            "action" => "custom",
            "id" => "api:" . $name
        ];

        foreach ($parameters as $key => $value) 
            $data[$key] = $value;
        
        return $this->post($data);
    }

    function post($data, $url = NULL){
        $curl = curl_init();

        if ($url == NULL){
            if (!$this->authorized)
                return false;

            $url = "https://" . $this->tracker . "/event";
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
