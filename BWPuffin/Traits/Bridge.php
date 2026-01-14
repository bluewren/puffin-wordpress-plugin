<?php
namespace BWPuffin\Traits;

trait Bridge
{
    function getClientToken($state = 'init')
    {
        $result = false;
        $new_client_token = '';

        $token_url = get_option('options_puffin_token_url');
        $client_id = get_option('options_puffin_client_id');
        $client_secret = get_option('options_puffin_client_secret');
        $client_token = get_option('options_puffin_client_token');

        if($state == 'reset') {
            $client_token = '';
            update_option('options_puffin_client_token', '');

            // only allow one renewal attempt
            $this->second_attempt = true;
        }

        if (!$client_token)
        {
            $url = $token_url;
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL             => $url,
                CURLOPT_RETURNTRANSFER  => true,
                CURLOPT_ENCODING        => '',
                CURLOPT_MAXREDIRS       => 10,
                CURLOPT_TIMEOUT         => 0,
                CURLOPT_FOLLOWLOCATION  => true,
                CURLOPT_HTTP_VERSION    => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST   => 'POST',
                CURLOPT_POSTFIELDS      => 'grant_type=client_credentials&client_id='.$client_id.'&client_secret='.$client_secret.'',
                CURLOPT_HTTPHEADER      => [
                    'Content-Type: application/x-www-form-urlencoded'
                ],
            ]);

            $result = curl_exec($curl);
            curl_close($curl);
        }

        if ($result != false)
        {
            $result = json_decode($result, true);
            $new_client_token = $result['access_token'] ?? '';
        }

        if($new_client_token){
            update_option('options_puffin_client_token', $new_client_token);
            $client_token = $new_client_token;
        }

        return $client_token;
    }


    function initRequest($data = [],  $ctype = 'application/json')
    {
        $token = $this->getClientToken();

        $data['type'] ?? false ?: $data['type'] = 'GET';

        $success = true;
        $http_code = 0;

        $ch = curl_init($data['url']);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Accept: application/json',
            'Authorization: Bearer ' . $token,
            'Content-Type: '.$ctype,
        ]);
       
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $data['type']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 

        if($data['body'] ?? false){
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data['body']));
        }

        $outputget = curl_exec($ch);

        if($outputget === false){
            $array = curl_error($ch);
        } else {
            $array = $outputget;
    
            switch ($http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
                case 200: 
                    // All is good - success
                break;
                case 201: 
                    // All is good - created
                break;
                case 401: 
                    // Unauthenticated
                    $success = false;
                break;
                case 403:
                    // Unauthorised
                    $success = false;
                break;
                case 404:
                    // not found
                    $success = false;
                break;
                default:
                    // everything else 
                    $success = false; 
            }
        }

        curl_close($ch);

        if($success){
            return json_decode($array, true);
        } else {
            // if Unauthorised/Unauthenticated try for a new token
            if($http_code == 401 && !$this->second_attempt || $http_code == 403 && !$this->second_attempt){
                $this->getClientToken('reset');
                return $this->get_request($data, $ctype);
            }

            return json_decode($array, true);
        }
    }
}