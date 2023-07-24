<?php

use phpDocumentor\Reflection\Types\Null_;

defined('BASEPATH') OR exit('No direct script access allowed');
        
class User extends CI_Controller {


    public function __construct()
    {
        parent::__construct();
        $this->load->library('restapi');
    }

    public function index($id = NULL)
    {
        if ($this->input->method() == 'post') {
            $data = json_decode(file_get_contents('php://input'), true);
            $data ["created_at"] = date("Y-m-d H:i:s");

            if(empty($data['id']) || empty($data['email']) || empty($data['first_name']) || empty($data['last_name']) || empty($data['avatar']))
            {
                return $this->restapi->formatter('failed', '04', ['message' => 'Lengkapi data!']);  
            }
            
            $cek_id = $this->db->where('id', $data['id'])->get('users')->num_rows();
            if ($cek_id > 0) {
                return $this->restapi->formatter('failed', '04', ['message' => 'ID sudah ada.']); 
            }
            $this->db->insert('users', $data);
            $this->restapi->formatter('success', '00', $data);
            
        } elseif ($this->input->method() == 'put') {
           
            $data = json_decode(file_get_contents('php://input'), true);
            $data ["updated_at"] = date("Y-m-d H:i:s");

            if(empty($data['id']) || empty($data['email']) || empty($data['first_name']) || empty($data['last_name']) || empty($data['avatar']))
            {
                return $this->restapi->formatter('failed', '04', ['message' => 'Lengkapi data!']);  
            }
            
            $cek_id = $this->db->where('id', $data['id'])->where('deleted_at', NULL)->get('users')->num_rows();
            if ($cek_id < 1) {
                return $this->restapi->formatter('failed', '04', ['message' => 'ID tidak ditemukan.']); 
            }
            
            $this->db->where('id', $data['id']);
            $this->db->update('users', $data);
            $this->restapi->formatter('success', '00', $data);

        } else if ($this->input->method() == 'delete') {
            $this->restapi->basic_auth();
            $data = json_decode(file_get_contents('php://input'), true);
            $data ["deleted_at"] = date("Y-m-d H:i:s");
            if(empty($data['id']))
            {
                return $this->restapi->formatter('failed', '04', ['message' => 'ID belum di isi!']);  
            }
            $cek_id = $this->db->where('id', $data['id'])->get('users')->num_rows();
            if ($cek_id < 1) {
                return $this->restapi->formatter('failed', '04', ['message' => 'ID tidak ditemukan.']); 
            }
            $this->db->where('id', $data['id']);
            $this->db->update('users', $data);
            $this->restapi->formatter('success', '00', $data);
        } else if ($this->input->method() == 'get' && $id != NULL) {
            $data = $this->db->where('id', $id)->where('deleted_at', NULL)->get('users')->row_array();
            $this->restapi->formatter('success', '00', $data);
        } else {
            $this->restapi->method('get');
            $data = $this->db->where('deleted_at', NULL)->get('users')->result_array();
            $this->restapi->formatter('success', '00', $data);
        }
    
    }

    public function fetch()
    {
        $this->restapi->method('get');

        if(!$this->input->get('page')){
            $this->restapi->formatter('failed', '04', ['message' => 'missing query parameter name']);
            die;
        }

        $page =  $this->input->get('page');


        $curl = curl_init();
        curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://reqres.in/api/users?page='. $page,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 0,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'GET',
        ));

        $response = curl_exec($curl);

        curl_close($curl);
        $response = json_decode($response, 1);
        $data = $response['data'];

        //get data di table users
        $ex_ids = $this->db->get('users')->result_array();


        //  tampung id ke variable  
        $ex_id_array = array();
        foreach ($ex_ids as $row) {
            $ex_id_array[] = $row['id'];
        }


        //  tampung data yang tidak ada pada table berdasarkan id pada variable ex_id_array
        $filtered_data = array();
        foreach ($data as $val) {
            if (!in_array($val['id'], $ex_id_array)) {
                $filtered_data[] = [
                            "id"=> $val['id'],
                            "email"=> $val['email'],
                            "first_name"=> $val['first_name'],
                            "last_name"=> $val['last_name'],
                            "avatar"=> $val['avatar'],
                            "created_at" => date("Y-m-d H:i:s")
                        ];
            }
        }

        //lakukan insert ke database
        if (!empty($filtered_data)) {
            $this->db->insert_batch('users', $filtered_data);
            }
            
        $this->restapi->formatter('success', '00', [$data]);
    }
        
}
        
    /* End of file  user.php */
        
                        