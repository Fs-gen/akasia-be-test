<?php


function cek_jwt($jwt)
{
    // get instance
    $ci =& get_instance();
    $cek = $ci->db->where('token', $jwt)->get('tr_token')->row_array();
    if ($cek) {
        return $jwt;
    } else {
        return false;
    }

}

function cek_auth($code)
{
    // get instance
    $ci =& get_instance();

    if ($code === "3cdcnTiBsl") {
        return true;
    } else {
        return false;
    }

}
// function cek_auth($user, $password)
// {
//     // get instance
//     $ci =& get_instance();
//     $cek = $ci->db->where('user', $user)->where('password', $password)->get('api_config')->row_array();
//     if ($cek) {
//         return true;
//     } else {
//         return false;
//     }

// }

function rupiah($total)
{
    return number_format($total, 0);
}

function generateRandomString($length = 10)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $randomString;
}

/* End of file api.php */