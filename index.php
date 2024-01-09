<?php
// session_start();
header('Content-Type: application/json');
header("Access-Control-Allow-Origin: *");
// header("Access-Control-Allow-Headers: *");
// header("Access-Control-Allow-Credentials: *");
// header("Access-Control-Max-Age: 60");
// header('Access-Control-Allow-Methods: *');
header("Access-Control-Allow-Headers: X-Requested-With, Content-Type, Origin, Cache-Control, Pragma, Authorization, Accept, Accept-Encoding");
set_time_limit(0);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include_once 'Request.php';
include_once 'Router.php';
$router = new Router(new Request);

$router->post('/empire/auth', function ($request)
{
    $req = $request->getBody();
    $status = $request->req($req, 'auth');
    $data = json_decode($status);
    if(($status)&&($data->result=='success')){
        http_response_code(200);
        // $tok = array(
        //     "status" => 1,
        //     "errors" => array("message"=>$data->result)
        // );  
    }else{
        // header('HTTP/1.1 400 Bad/Invalid or unprocessable request');
        // http_response_code(400);
        // http_response_code();
        http_response_code(200);
        // $tok = array(
        //     "status" => 0,
        //     "errors" => array("message"=>$data->error)
        // ); 
    }
    return json_encode($data);
});

$router->post('/empire/caregivers', function ($request)
{
    $req = $request->getBody();
    $status = $request->req($req, 'caregivers');
    $data = json_decode($status);
    
    if(($status)&&($data->result=='success')){
        http_response_code(200);
        // $tok = array(
        //     "status" => 1,
        //     "errors" => array("message"=>$data->result)
        // );  
    }else{
        // header('HTTP/1.1 400 Bad/Invalid or unprocessable request');
        // http_response_code(400);
        // http_response_code();
        http_response_code(200);
        // $tok = array(
        //     "status" => 0,
        //     "errors" => array("message"=>$data->error)
        // ); 
    }
    return json_encode($data);
});

$router->post('/empire/misc', function ($request)
{
    $req = $request->getBody();
    $status = $request->req($req, 'misc');
    $data = json_decode($status);
    if(($status)&&($data->result=='success')){
        http_response_code(200);
        // $tok = array(
        //     "status" => 1,
        //     "errors" => array("message"=>$data->result)
        // );  
    }else{
        // header('HTTP/1.1 400 Bad/Invalid or unprocessable request');
        // http_response_code(400);
        // http_response_code();
        http_response_code(200);
        // $tok = array(
        //     "status" => 0,
        //     "errors" => array("message"=>$data->error)
        // ); 
    }
    return json_encode($data);
});

$router->post('/empire/members', function ($request)
{
    $req = $request->getBody();
    $status = $request->req($req, 'members');
    $data = json_decode($status);
    if(($status)&&($data->result=='success')){
        http_response_code(200);
        // $tok = array(
        //     "status" => 1,
        //     "errors" => array("message"=>$data->result)
        // );  
    }else{
        // header('HTTP/1.1 400 Bad/Invalid or unprocessable request');
        // http_response_code(400);
        // http_response_code();
        http_response_code(200);
        // $tok = array(
        //     "status" => 0,
        //     "errors" => array("message"=>$data->error)
        // ); 
    }
    return json_encode($data);
});


$router->post('/empire/visit', function ($request)
{
    $req = $request->getBody();
    $status = $request->req($req, 'visit');
    $data = json_decode($status);
    if(($status)&&($data->result=='success')){
        http_response_code(200);
        // $tok = array(
        //     "status" => 1,
        //     "errors" => array("message"=>$data->result)
        // );  
    }else{
        // header('HTTP/1.1 400 Bad/Invalid or unprocessable request');
        // http_response_code(400);
        // http_response_code();
        http_response_code(200);
        // $tok = array(
        //     "status" => 0,
        //     "errors" => array("message"=>$data->error)
        // ); 
    }
    return json_encode($data);
});



// $router->get('/api/pickups', function ($request)
// {
//     $req = $request->getBody();
//     $pickups = $request->req($req, 'pickups');
//     $data = json_decode($pickups);
//     if(($pickups)&&($data->response==200)){
//         $tok = array(
//             "status" => 1,
//             "data" => $data,
//         );  
//     }else{
//         $tok = array(
//             "status" => 0,
//             "data" => $data,
//         ); 
//     }
//     return json_encode($tok);
// });