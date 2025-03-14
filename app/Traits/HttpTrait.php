<?php

namespace App\Traits;
use App\Exceptions;
use GuzzleHttp\Client;

trait HttpTrait
{
    public function http($url){
        $client = new Client();
        try {
            $response = $client->request('GET', $url, [
                'headers' => [
                    'Accept' => 'application/json',
                ],
                'verify' => false,
            ]);
            $result = $response->getBody()->getContents();
            return $result;
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            echo $e->getMessage();
        }
        
    }
}
