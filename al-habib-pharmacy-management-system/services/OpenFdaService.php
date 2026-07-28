<?php

class OpenFdaService
{
    private $baseUrl = "https://api.fda.gov/drug/ndc.json";
    private $apiKey = "";

    public function __construct($apiKey = "")
    {
        $this->apiKey = $apiKey;
    }

    public function fetchMedicines($limit = 100, $skip = 0, $search = "")
    {
        $params = [
            "limit" => $limit,
            "skip" => $skip
        ];

        if (!empty($search)) {
            $params["search"] = $search;
        }

        if (!empty($this->apiKey)) {
            $params["api_key"] = $this->apiKey;
        }

        $url = $this->baseUrl . "?" . http_build_query($params);

        $response = $this->sendRequest($url);

        if ($response === false) {
            return [];
        }

        $data = json_decode($response, true);

        if (!isset($data["results"])) {
            return [];
        }

        return $data["results"];
    }

    private function sendRequest($url)
    {
        if (function_exists("curl_init")) {
            $curl = curl_init();

            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_TIMEOUT, 20);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($curl);

            curl_close($curl);

            return $response;
        }

        return @file_get_contents($url);
    }
}

?>