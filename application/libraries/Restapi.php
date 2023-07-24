<?php

defined('BASEPATH') or exit('No direct script access allowed');

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class RestApi
{
    //constructor
    public function __construct()
    {
        $ci = &get_instance();
        header('Access-Control-Allow-Credentials: true');
        header("Access-Control-Allow-Origin: *");
        header("Access-Control-Allow-Headers: *");
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method, Access-Control-Request-Headers, Authorization");
        header('Content-Type: application/json; charset=utf-8');
        $method = $_SERVER['REQUEST_METHOD'];
        if ($method == "OPTIONS") {
            header('Access-Control-Allow-Origin: *');
            header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method,Access-Control-Request-Headers, Authorization");
            header("HTTP/1.1 200 OK");
            exit(0);
        }
        // ip whitelist////////////////

        // $ip = $ci->input->ip_address();
        // $data = explode(',', config('ip_whitelist'));
        // if (!in_array($ip, $data, true)) {
        //     $data = array(
        //         'status_code' => "01",
        //         'message' => 'IP Address Not Allowed!'
        //     );
        //     header('Content-Type: application/json');
        //     http_response_code(401);
        //     echo json_encode($data);
        //     exit;
        // }

    }

    public function method($method = 'GET')
    {

        if ($_SERVER['REQUEST_METHOD'] != strtoupper($method)) {
            $data = array(
                'status_code' => "03",
                'message' => 'Method Not Allowed!'
            );
            header('Content-Type: application/json');
            http_response_code(405);
            echo json_encode($data);
            exit;
        }
    }

    public function formatter($message = 'success', $response_code = '00', $data_res = null)
    {
        $ci = &get_instance();
        // layer 2 Auth
        $data = array(
            'status_code' => $response_code,
            'message' => $message,
        );

        if ($data_res != null) {
            $data['data'] = $data_res;
            array_push($data['data']);
        }
        header('Content-Type: application/json');
        http_response_code(200);
        echo json_encode($data);
    }


    // filter jwt auth

    public function auth()
    {
        $ci = &get_instance();
        //get secret key
        if (!isset($ci->input->request_headers()['Authorization'])) {
            $data = array(
                'status_code' => "02",
                'message' => 'Unauthorized'
            );
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode($data);
            exit;
        }

        // jwt
        $jwt = $ci->input->request_headers()['Authorization'];
        //cocokkan jwt dengan yg ada di database
        $jwt = explode(' ', $jwt)[1];
        $jwt = cek_jwt($jwt);
        

        if ($jwt === false) {
            $data = array(
                'status_code' => "02",
                'message' => 'Unauthorized'
            );
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode($data);
            exit;
        }
        $key = config('secret_key');
        try {
            return JWT::decode($jwt, new Key($key, 'HS256'));
        } catch (\Throwable $th) {
            $data = array(
                'status_code' => "02",
                'message' => 'Token Invalid'
            );
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode($data);
            exit;
        }
    }

    //function auth basic auth
    public function basic_auth()
    {
        $ci = &get_instance();

        //cek apakah ada header authorization
        if (!isset($ci->input->request_headers()['Authorization'])) {
            $data = array(
                'status_code' => "02",
                'message' => 'Unauthorized'
            );
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode($data);
            exit;
        }

        //cek apakah cocok dengan database api_config table
        $auth = $ci->input->request_headers()['Authorization'];
        $auth = explode(' ', $auth)[0];
        $cek = cek_auth($auth);
        if ($cek === false) {
            $data = array(
                'status_code' => "02",
                'message' => 'Unauthorized'
            );
            header('Content-Type: application/json');
            http_response_code(401);
            echo json_encode($data);
            exit;
        }

    }
}

/* End of file RestApi.php */