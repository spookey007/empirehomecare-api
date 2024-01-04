<?php
include_once 'IRequest.php';
include_once 'db.php';

class Request implements IRequest
{
    function __construct()
    {
        $this->bootstrapSelf();
    }

    private function bootstrapSelf()
    {
        foreach ($_SERVER as $key => $value)
        {
            $this->{$this->toCamelCase($key) } = $value;
        }
    }

    private function toCamelCase($string)
    {
        $result = strtolower($string);

        preg_match_all('/_[a-z]/', $result, $matches);

        foreach ($matches[0] as $match)
        {
            $c = str_replace('_', '', strtoupper($match));
            $result = str_replace($match, $c, $result);
        }

        return $result;
    }

    public function getBody()
    {
        if ($this->requestMethod === "GET")
        {
            $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
            $url_components = parse_url($actual_link);
            parse_str($url_components['query'], $params);
            return json_decode(json_encode($params));
        }

        if ($this->requestMethod == "POST")
        {
            $params = (array)json_decode(file_get_contents('php://input') , true);
            return $params;
        }
    }
    public function req($req, $case)
    {
        switch ($case)
        {
            case "auth":
                return $this->verify_action('POST', $req, $case);
            break;
            case "status":
                return $this->verify_action('POST', $req, $case);
            break;
            case "movexstatus":
                return $this->verify_action('POST', $req, $case);
            break;
            case "pickups":
                return $this->verify_action('GET', $req, $case);
            break;
            case "city_list":
                return $this->verify_action('GET', $req, $case);
            break;
            case "book":
                return $this->verify_action('POST', $req, $case);
            break;
            case "track":
                return $this->verify_action('POST', $req, $case);
            break;
            case "address-label":
                return $this->verify_action('POST', $req, $case);
            break;
            case "cancel":
                return $this->verify_action('POST', $req, $case);
            break;
            default:
                return "Something went wrong !";
        }

    }
    public function verify_action($method, $params, $case)
    {
        $add_params = '';
        switch ($case)
        {
            case "auth":
                return $this->get_data('SELECT', $params, $case);
            break;
            case "status":
                return $this->get_data('SELECT', $params, $case);
            break;
            case "movexstatus":
                return $this->get_data('SELECT', $params, $case);
            break;
            case "pickups":
                return $this->get_data('SELECT', $params, $case);
            break;
            case "city_list":
                return $this->get_data('SELECT', $params, $case);
            break;
            case "book":
                return $this->get_data('SELECT', $params, $case);
            break;
            case "track":
                return $this->get_data('SELECT', $params, $case);
            break;
            case "address-label":
                return $this->get_data('SELECT', $params, $case);
            break;
            case "cancel":
                return $this->get_data('SELECT', $params, $case);
            break;
            default:
                return "Something went wrong !";
        }
    }
    
    public function get_data($method, $params, $case)
    {
        $add_params = '';
        switch ($case)
        {
            case "auth":
                try
                {
                    $database = new Connection();
                    $db = $database->openConnection();
                    
                    
                    $sql = "SELECT * FROM pickup_information where account_number = $params " ;
                    $pickups = array();
                    foreach ($db->query($sql) as $row) {
                        $data = array(
                            "city_code"                =>      $row['city_code'],
                            "pickup_code"              =>      $row['pickup_code'],
                            "pickup_name"              =>      $row['pickup_name'],
                            "pickup_contact"           =>      $row['pickup_contact'],
                            "pickup_email"             =>      $row['pickup_email'],
                            "pickup_address"           =>      $row['pickup_address']
                    );
                        array_push($pickups,$data);
                    }
                    $sql = "SELECT * FROM stations where status = 'Y'" ;
                    $station = array();
                    foreach ($db->query($sql) as $row) {
                        $data = array(
                            "station_id"        =>      $row['station_id'],
                            "station_name"      =>      $row['station_name'],
                            "city_id"           =>      $row['city_id'],
                            "city_code"         =>      $row['city_code'],
                            "address"           =>      $row['address'],
                            "status"            =>      $row['status']
                    );
                        array_push($station,$data);
                    }
                    $sql = "SELECT ca.*,ui.city_code FROM `customer_account` ca left join user_information ui on ui.account_number = ca.account_number  where ca.account_number = $params " ;
                    $res = array();
                    foreach ($db->query($sql) as $row) {
                        $data = array(
                            "account_number"    =>      $row['account_number'],
                            "account_name"      =>      $row['account_name'],
                            "mobile_number"     =>      $row['account_cell'],
                            "email_address"     =>      $row['account_email'],
                            "city_code"         =>      $row['city_code'],
                            "api_key"           =>      $row['api_key'],
                            "pickups"           =>      $pickups,
                            "stations"           =>     $station
                            );
                        array_push($res,$data);
                    }
                    return $res;
                    $database->closeConnection();
                }
                catch (PDOException $e)
                {
                    return "There is some problem in connection: " . $e->getMessage();
                    
                }
            break;
         case "status":
            try

                {
                    date_default_timezone_set('Asia/Karachi');
                    $database = new Connection();

                    $db = $database->openConnection();

                    $requestHeaders = apache_request_headers();

                    $auth = $requestHeaders['Authorization'];

                    $success_array=array();

                    $error_array=array();

                    if($auth=='Qxd9VPGEifruWhglJ7tMB'){

                       $this->logger('true','Cron Started','-','log','Leo','-');

                                  if(isset($params['data'])){

                            $query = $db->prepare("Select ifnull(max(sheet_number),0)+1 as no From delivery_master");

                                        $query->execute();

                                        $row = $query->fetch(PDO::FETCH_ASSOC);

                                        $id = $row['no'];
                                        $current_date = date("Y-m-d H:i:s");

                                        $dm = $db->prepare("Insert Into delivery_master(sheet_number, sheet_date, rider_code, route_code, create_by,city_code) Values (?,?,?,?,?,?)");

                                        $rm = $dm->execute([$id,$current_date,'1', '1','CRON','1']);

                            $json_log = json_encode($params['data']);

                            $this->logger('true','Response From Leopard',$json_log,'log','Leo','-');

                            $record_count=1;

                                foreach ($params['data'] as $param) {
                                        // echo "<pre>";print_r($param);
                                    //failed attempt/hold for self coll
                                if($param['status']=='PN1' || $param['status']=='PN2'){

                                    $tpcnno = $param['cn_number'];

                                    // Get Shipment No

                                    $sql = $db->prepare("Select shipment_no,tpcnno,tpcode From shipments  Where tpcode ='L' and shipment_no not in  

                                    (SELECT shipment_no from detail_table where master_type='DV' AND delivery_status_code = 1) and tpcnno ='$tpcnno' ");

                                    $sql->execute();

                                    $result = $sql->fetch(PDO::FETCH_ASSOC);

                                    if($result){

                                        $shipment_no = $result['shipment_no'];

                                        $this->logger($shipment_no,'Failed Attempt Matched!','-','log','Leo','-');


                                        $current_date = date("Y-m-d H:i:s");

                                         $receiver_name =  preg_replace('/[^A-Za-z0-9\-]/', '', $param['receiver_name']);

                                        // Insert Detail
                                         $insert_query = "Insert into detail_table (master_no,master_type,shipment_no,enter_timestamp,peices,weight,delivery_status_code,update_timestamp,received_by) 
                                            Values ({$id},'DV',{$shipment_no},'$current_date',1,2,35,'$current_date','$receiver_name')";

                                            $this->logger($shipment_no,'Failed Attempt  Query!','-','log','Leo',$insert_query);

                                        // Insert Detail

                                        $dm = $db->prepare("Insert into detail_table (master_no,master_type,shipment_no,enter_timestamp,peices,weight,delivery_status_code,update_timestamp,received_by) Values (?,?,?,?,?,?,?,?,?)");

                                        $im = $dm->execute([$id,'DV',$shipment_no, $current_date,'1','2','35',$current_date,$receiver_name]);
                                        
                                        if($im){
                                             $this->logger($shipment_no,'Failed Attempt  Inserted!','-','log','Leo','-');
                                        }


                                         $del_status = [
                                            'new_delivery_status' => 35,
                                        ];

                                        $del_up =  "UPDATE shipments SET delivery_status=:new_delivery_status WHERE shipment_no = '$shipment_no' AND tpcnno ='$tpcnno'";

                                       $d = $db->prepare($del_up)->execute($del_status);


                                        $success_array[] = array(

                                            'record_row'    => $record_count,

                                            'message'       => 'Status Updated to Failed Attempt',

                                        );

                                    }else{

                                        $error_array[] = array(

                                            'record_row'   => $record_count,

                                            'message'  => 'Shipment Number Not Found',

                                        );

                                    }
                                    //delivered
                                }//delivered
                                else if($param['status']=='DV'){
                                        $tpcnno = $param['cn_number'];
                                         $sql = $db->prepare("Select shipment_no,tpcnno,tpcode From shipments Where  tpcode ='L' and shipment_no not in  

                                    (SELECT shipment_no from detail_table where master_type='DV' AND delivery_status_code = 1) and tpcnno ='$tpcnno' ");

                                    $sql->execute();

                                    $result = $sql->fetch(PDO::FETCH_ASSOC);
                                    // echo "<pre>";print_r($result);
                                    if($result){
                                         $shipment_no = $result['shipment_no'];
                                         $this->logger($shipment_no,'Delivered Matched!','-','log','Leo','-');
                                          $current_date = date("Y-m-d H:i:s");
                                           $receiver_name =  preg_replace('/[^A-Za-z0-9\-]/', '', $param['receiver_name']);
                                          
                                            $dm = $db->prepare("Insert into detail_table (master_no,master_type,shipment_no,enter_timestamp,peices,weight,delivery_status_code,update_timestamp,received_by) Values (?,?,?,?,?,?,?,?,?)");

                                        $im = $dm->execute([$id,'DV',$shipment_no, $current_date,'1','2','1',$current_date,$receiver_name]);
                                         if(!$im){
                                              $this->logger($shipment_no,'Delivered Inserted!','-','log','Leo','-');
                                        }
                                        else{
                                            $ne = $db->prepare("Insert into exception_cn (shipment_no,tpcnno,created_date) Values (?,?,?)");
                                                $in = $ne->execute([$shipment_no, $tpcnno,$current_date]);
                                        }

                                        $insert_query = "Insert into detail_table (master_no,master_type,shipment_no,enter_timestamp,peices,weight,delivery_status_code,update_timestamp,received_by) Values ({$id},'DV',{$shipment_no},'$current_date',1,2,1,'$current_date','$receiver_name')";
                                        //  $this->logger($shipment_no,'Delivered  Query!','-','log','Leo',$insert_query);
                                         $log_array = array(
                                                    'cn'=>$shipment_no,
                                                    'tpcnno'=>$tpcnno,
                                                    'insert_query'=>$insert_query
                                                );
                                                $this->json_logs(json_encode($log_array));
                                                 
                                       
                                         $del_status = [
                                            'new_delivery_status' => 1,
                                        ];

                                        $del_up =  "UPDATE shipments SET delivery_status=:new_delivery_status WHERE shipment_no = '$shipment_no' AND tpcnno ='$tpcnno'";

                                       $d = $db->prepare($del_up)->execute($del_status);
                                        $success_array[] = array(

                                            'record_row'    => $record_count,

                                            'message'       => 'Status Updated to Delivered',

                                        );


                                    }else{

                                        $error_array[] = array(

                                            'record_row'   => $record_count,

                                            'message'  => 'Shipment Number Not Found',

                                        );

                                    }
                                }
                                //arrived at dest
                                else if($param['status']=='AR'){
                                        $tpcnno = $param['cn_number'];
                                         $sql = $db->prepare("Select shipment_no,tpcnno,tpcode From shipments Where  tpcode ='L' and shipment_no not in  

                                    (SELECT shipment_no from detail_table where master_type='DV' AND delivery_status_code = 1) and tpcnno ='$tpcnno' ");

                                    $sql->execute();

                                    $result = $sql->fetch(PDO::FETCH_ASSOC);
                                    // echo "<pre>";print_r($result);
                                    if($result){
                                         $shipment_no = $result['shipment_no'];
                                         $this->logger($shipment_no,'Arrived At Destination Matched!','-','log','Leo','-');
                                          $current_date = date("Y-m-d H:i:s");
                                           $receiver_name =  preg_replace('/[^A-Za-z0-9\-]/', '', $param['receiver_name']);
                                          
                                            $dm = $db->prepare("Insert into detail_table (master_no,master_type,shipment_no,enter_timestamp,peices,weight,delivery_status_code,update_timestamp) Values (?,?,?,?,?,?,?,?)");

                                        $im = $dm->execute([$id,'DV',$shipment_no, $current_date,'1','2','36',$current_date]);
                                         if($im){
                                              $this->logger($shipment_no,'Arrived At Destination Inserted!','-','log','Leo','-');
                                        }
                                        
                                         $del_status = [
                                            'new_delivery_status' => 36,
                                        ];

                                        $del_up =  "UPDATE shipments SET delivery_status=:new_delivery_status WHERE shipment_no = '$shipment_no' AND tpcnno ='$tpcnno'";

                                       $d = $db->prepare($del_up)->execute($del_status);
                                        $success_array[] = array(

                                            'record_row'    => $record_count,

                                            'message'       => 'Status Updated to Arrived At Destination',

                                        );


                                    }else{

                                        $error_array[] = array(

                                            'record_row'   => $record_count,

                                            'message'  => 'Shipment Number Not Found',

                                        );

                                    }
                                }
                                //out for delivery
                                else if($param['status']=='AC'){

                                    $tpcnno = $param['cn_number'];

                                    // Get Shipment No

                                    $sql = $db->prepare("Select shipment_no,tpcnno,tpcode From shipments Where tpcode ='L' and shipment_no not in  

                                    (SELECT shipment_no from detail_table where master_type='DV' AND delivery_status_code = 1) and tpcnno ='$tpcnno' ");

                                    $sql->execute();

                                    $result = $sql->fetch(PDO::FETCH_ASSOC);

                                    if($result){

                                        $shipment_no = $result['shipment_no'];

                                       $this->logger($shipment_no,'Out For Delivery Matched!','-','log','Leo','-');
                                        $current_date = date("Y-m-d H:i:s");

                                        $receiver_name =  preg_replace('/[^A-Za-z0-9\-]/', '', $param['receiver_name']);

                                        // Insert Detail
                                         $insert_query = "Insert into detail_table (master_no,master_type,shipment_no,enter_timestamp,peices,weight,delivery_status_code,update_timestamp,received_by) Values ({$id},'DV',{$shipment_no},'$current_date',1,2,32,'$current_date','$receiver_name')";
                                        $this->logger($shipment_no,'Out for delivery  Query!','-','log','Leo',$insert_query);

                                        // Insert Detail

                                        $dm = $db->prepare("Insert into detail_table (master_no,master_type,shipment_no,enter_timestamp,peices,weight,delivery_status_code,update_timestamp) Values (?,?,?,?,?,?,?,?)");

                                        $im = $dm->execute([$id,'DV',$shipment_no, $current_date,'1','2','32',$current_date]);
                                        
                                        if($im){
                                             $this->logger($shipment_no,'Out For Delivery Inserted!','-','log','Leo','-');
                                        }else{
                                            $this->logger($shipment_no, $dm->errorInfo(),'-','log','Leo','-');
                                        }


                                        $del_status = [
                                            'new_delivery_status' => 32,
                                        ];

                                        $del_up =  "UPDATE shipments SET delivery_status=:new_delivery_status WHERE shipment_no = '$shipment_no' AND tpcnno ='$tpcnno'";

                                       $d = $db->prepare($del_up)->execute($del_status);

                                        $success_array[] = array(

                                            'record_row'    => $record_count,

                                            'message'       => 'Status Updated to Out For Delivery',

                                        );

                                    }else{

                                        $error_array[] = array(

                                            'record_row'   => $record_count,

                                            'message'  => 'Shipment Number Not Found',

                                        );

                                    }
                                }
                                // ready to return
                                else if($param['status']=='NR'){

                                    $tpcnno = $param['cn_number'];

                                    // Get Shipment No

                                    $sql = $db->prepare("Select shipment_no,tpcnno,tpcode From shipments Where tpcode ='L' and shipment_no not in  

                                    (SELECT shipment_no from detail_table where master_type='DV' AND delivery_status_code = 1) and tpcnno ='$tpcnno' ");

                                    $sql->execute();

                                    $result = $sql->fetch(PDO::FETCH_ASSOC);

                                    if($result){

                                        $shipment_no = $result['shipment_no'];

                                        $this->logger($shipment_no,'Ready to return Matched!','-','log','Leo','-');

                                        $current_date = date("Y-m-d H:i:s");

                                    
                                        $receiver_name =  preg_replace('/[^A-Za-z0-9\-]/', '', $param['receiver_name']);

                                        // Insert Detail
                                         $insert_query = "Insert into detail_table (master_no,master_type,shipment_no,enter_timestamp,peices,weight,delivery_status_code,update_timestamp,received_by) 
                                                Values ({$id},'DV',{$shipment_no},'$current_date',1,2,33,'$current_date','$receiver_name')";
                                                $this->logger($shipment_no,'Ready to return  Query!','-','log','Leo',$insert_query);

                                        // Insert Detail

                                        $dm = $db->prepare("Insert into detail_table (master_no,master_type,shipment_no,enter_timestamp,peices,weight,delivery_status_code,update_timestamp) Values (?,?,?,?,?,?,?,?)");

                                        $im = $dm->execute([$id,'DV',$shipment_no, $current_date,'1','2','33',$current_date]);
                                        
                                        if($im){
                                              $this->logger($shipment_no,'Ready To Return Inserted!','-','log','Leo','-');
                                        }

                                        
                                        
                                        $del_status = [
                                            'new_delivery_status' => 33,
                                        ];

                                        $del_up =  "UPDATE shipments SET delivery_status=:new_delivery_status WHERE shipment_no = '$shipment_no' AND tpcnno ='$tpcnno'";

                                       $d = $db->prepare($del_up)->execute($del_status);


                                        $success_array[] = array(

                                            'record_row'    => $record_count,

                                            'message'       => 'Status Updated to Ready To Return',

                                        );

                                    }else{

                                        $error_array[] = array(

                                            'record_row'   => $record_count,

                                            'message'  => 'Shipment Number Not Found',

                                        );

                                    }
                                } 
                                //return dispatched
                                else if($param['status']=='RO'){
                                        $tpcnno = $param['cn_number'];
                                         $sql = $db->prepare("Select shipment_no,tpcnno,tpcode From shipments Where  tpcode ='L' and shipment_no not in  

                                    (SELECT shipment_no from detail_table where master_type='DV' AND delivery_status_code = 1) and tpcnno ='$tpcnno' ");

                                    $sql->execute();

                                    $result = $sql->fetch(PDO::FETCH_ASSOC);
                                    // echo "<pre>";print_r($result);
                                    if($result){
                                         $shipment_no = $result['shipment_no'];
                                         $this->logger($shipment_no,'Return Dispatched Matched!','-','log','Leo','-');
                                          $current_date = date("Y-m-d H:i:s");
                                           $receiver_name =  preg_replace('/[^A-Za-z0-9\-]/', '', $param['receiver_name']);
                                          
                                            $dm = $db->prepare("Insert into detail_table (master_no,master_type,shipment_no,enter_timestamp,peices,weight,delivery_status_code,update_timestamp) Values (?,?,?,?,?,?,?,?)");

                                        $im = $dm->execute([$id,'DV',$shipment_no, $current_date,'1','2','37',$current_date]);
                                         if($im){
                                              $this->logger($shipment_no,'Return Dispatched Inserted!','-','log','Leo','-');
                                        }
                                        
                                         $del_status = [
                                            'new_delivery_status' => 37,
                                        ];

                                        $del_up =  "UPDATE shipments SET delivery_status=:new_delivery_status WHERE shipment_no = '$shipment_no' AND tpcnno ='$tpcnno'";

                                       $d = $db->prepare($del_up)->execute($del_status);
                                        $success_array[] = array(

                                            'record_row'    => $record_count,

                                            'message'       => 'Status Updated to Return Dispatched By Station',

                                        );


                                    }else{

                                        $error_array[] = array(

                                            'record_row'   => $record_count,

                                            'message'  => 'Shipment Number Not Found',

                                        );

                                    }
                                }

                                

                            $res = array(

                                "response" =>true,

                                "message" => array( "success" => $success_array,"error"=>$error_array),

                            );

                            $this->logger('true',$res,'-','log','Leo','-');
                         }

                        }else{

                            $res = array(

                                "response" =>false,

                                "message" =>"Unable to Push Status, Data is empty."

                            );

                            $this->logger('false',$res,'Unable to Push Status, Data is empty.','log','Leo','-');

                        }

                    }else{

                        $res = array(

                            "response" =>false,

                            "message" =>"Invalid API Key"

                        );

                      $this->logger('false',$res,'Invalid API Key','log','Leo','-');

                    }

                    $this->logger('true','Cron Closed','-','log','Leo','-');

                    return json_encode($res);

                    $database->closeConnection();

                }

                catch (PDOException $e)

                {


                    header('HTTP/1.1 401 Unauthorized');

                    http_response_code(401);

                    http_response_code();

                    $error = array(

                        "message" =>"Bad/Invalid or Unprocessable Request"

                    );

                    $tok = array(

                        "status" => 0,

                        "errors" => json_decode($error)

                    );

                    return $tok;

                    

                }finally{
                    $database->closeConnection();
                }

            break;
             case "movexstatus":

                try

                {
                    date_default_timezone_set('Asia/Karachi');
                    $database = new Connection();
                    $db = $database->openConnection();
                    $requestHeaders = apache_request_headers();
                    $auth = $requestHeaders['Authorization'];
                    $success_array=array();

                    $error_array=array();
                    if($auth=='H3Lfs8qeGmnCPbkBAQTflyCo'){
                        $this->logger('true','Cron Started','-','log','MX');

                        if(!empty($params['data'])){

                            $json_log = json_encode($params['data']);

                            $this->logger('true','Response From Movex',$json_log,'log','MX');

                            $record_count=1;
                            $failed_attempt = array('CA','CNA','ICA','UL','Out of Cash','FC','No Response','Mobile Off','SC','COC','HIO','HFC');

                            foreach ($params['data'] as $param) {
                                //delivered
                                if($param['status_code']=='OK'){

                                    $tpcnno = $param['cn_number'];

                                    // Get Shipment No

                                    $sql = $db->prepare("Select shipment_no,tpcnno,tpcode From shipments WHERE tpcode ='MX' and  shipment_no not in  

                                    (SELECT shipment_no from detail_table where master_type='DV'  AND delivery_status_code =1) and tpcnno ='$tpcnno'  ");

                                    $sql->execute();

                                    $result = $sql->fetch(PDO::FETCH_ASSOC);

                                    if($result){

                                        $shipment_no = $result['shipment_no'];

                                        $this->logger($shipment_no,'Delivered Matched!','-','log','MX');

                                        // Get Max Number

                                        $query = $db->prepare("Select ifnull(max(sheet_number),0)+1 as no From delivery_master");

                                        $query->execute();

                                        $row = $query->fetch(PDO::FETCH_ASSOC);

                                        $id = $row['no'];

                                        // Insert Delivery Master

                                        $current_date = date("Y-m-d H:i:s");

                                        $dm = $db->prepare("Insert Into delivery_master(sheet_number, sheet_date, rider_code, route_code, create_by,city_code) Values (?,?,?,?,?,?)");

                                        $rm = $dm->execute([$id,$current_date,'1', '1','CRON','1']);
                                        
                                         

                                        // Insert Detail

                                        $dm = $db->prepare("Insert into detail_table (master_no,master_type,shipment_no,enter_timestamp,peices,weight,delivery_status_code,update_timestamp,received_by) Values (?,?,?,?,?,?,?,?,?)");

                                        $im = $dm->execute([$id,'DV',$shipment_no, $current_date,'1','2','1',$current_date,$param['consignee_name']]);
                                        
                                        if($im){
                                             $this->logger($shipment_no,'Delivered Inserted!','-','log','MX');
                                        }
                                        
                                        

                                        // Update Shipment

                                        // $sql = "Update shipments set current_status = 1 WHERE shipment_no='$shipment_no' AND tpcnno ='$tpcnno'";

                                        // $r = $db->prepare($sql)->execute($updata);
                                        $del_status = [
                                            'new_delivery_status' => 1,
                                        ];

                                        $del_up =  "UPDATE shipments SET delivery_status=:new_delivery_status WHERE shipment_no = '$shipment_no' AND tpcnno ='$tpcnno'";
                                        $d = $db->prepare($del_up)->execute($del_status);

                                        $success_array[] = array(

                                            'record_row'    => $record_count,

                                            'message'       => 'Status Updated to Delivered',

                                        );

                                    }else{

                                        $error_array[] = array(

                                            'record_row'   => $record_count,

                                            'message'  => 'Shipment Number Not Found',

                                        );

                                    }
                                        //out for delivery
                                }else if($param['status_code']=='Process'){

                                    $tpcnno = $param['cn_number'];

                                    // Get Shipment No

                                    $sql = $db->prepare("Select shipment_no,tpcnno,tpcode From shipments WHERE tpcode ='MX' and  shipment_no not in  

                                    (SELECT shipment_no from detail_table where master_type='DV'  AND delivery_status_code =1) and tpcnno ='$tpcnno' ");

                                    $sql->execute();

                                    $result = $sql->fetch(PDO::FETCH_ASSOC);

                                    if($result){

                                        $shipment_no = $result['shipment_no'];
                                       

                                        $this->logger($shipment_no,'Out For Delivery Matched!','-','log','MX');

                                        // Get Max Number

                                        $query = $db->prepare("Select ifnull(max(sheet_number),0)+1 as no From delivery_master");

                                        $query->execute();

                                        $row = $query->fetch(PDO::FETCH_ASSOC);

                                        $id = $row['no'];

                                        // Insert Delivery Master

                                        $current_date = date("Y-m-d H:i:s");

                                        $dm = $db->prepare("Insert Into delivery_master(sheet_number, sheet_date, rider_code, route_code, create_by,city_code) Values (?,?,?,?,?,?)");

                                        $rm = $dm->execute([$id,$current_date,'1', '1','CRON','1']);

                                        // Insert Detail

                                        $dm = $db->prepare("Insert into detail_table (master_no,master_type,shipment_no,enter_timestamp,peices,weight,delivery_status_code,update_timestamp,received_by) Values (?,?,?,?,?,?,?,?,?)");

                                        $im = $dm->execute([$id,'DV',$shipment_no, $current_date,'1','2','32',$current_date,$param['consignee_name']]);
                                        
                                         if($im){
                                             $this->logger($shipment_no,'Out For Delivery Inserted!','-','log','MX');
                                        }

                                        // Update Shipment

                                        // $updata = [
                                        //     'new_current_status' => 1,
                                        // ];

                                        // $sql = "UPDATE shipments SET current_status=:new_current_status WHERE shipment_no = '$shipment_no' AND tpcnno ='$tpcnno'";

                                        // $r = $db->prepare($sql)->execute($updata);

                                        $del_status = [
                                            'new_delivery_status' => 32,
                                        ];

                                        $del_up =  "UPDATE shipments SET delivery_status=:new_delivery_status WHERE shipment_no = '$shipment_no' AND tpcnno ='$tpcnno'";

                                       $d = $db->prepare($del_up)->execute($del_status);
                                        $success_array[] = array(

                                            'record_row'    => $record_count,

                                            'message'       => 'Status Updated to Out For Delivery',

                                        );

                                    }else{

                                        $error_array[] = array(

                                            'record_row'   => $record_count,

                                            'message'  => 'Shipment Number Not Found',

                                        );

                                    }

                                }
                                //ready to return
                                else if($param['status_code']=='Return in process'){

                                    $tpcnno = $param['cn_number'];

                                    // Get Shipment No

                                    $sql = $db->prepare("Select shipment_no,tpcnno,tpcode From shipments WHERE tpcode ='MX' and  shipment_no not in  

                                    (SELECT shipment_no from detail_table where master_type='DV' AND delivery_status_code = 1) and tpcnno ='$tpcnno' ");

                                    $sql->execute();

                                    $result = $sql->fetch(PDO::FETCH_ASSOC);

                                    if($result){

                                        $shipment_no = $result['shipment_no'];

                                        $this->logger($shipment_no,'Ready to Return Matched!','-','log','MX');

                                        // Get Max Number

                                        $query = $db->prepare("Select ifnull(max(sheet_number),0)+1 as no From delivery_master");

                                        $query->execute();

                                        $row = $query->fetch(PDO::FETCH_ASSOC);

                                        $id = $row['no'];

                                        // Insert Delivery Master

                                        $current_date = date("Y-m-d H:i:s");

                                        $dm = $db->prepare("Insert Into delivery_master(sheet_number, sheet_date, rider_code, route_code, create_by,city_code) Values (?,?,?,?,?,?)");

                                        $rm = $dm->execute([$id,$current_date,'1', '1','CRON','1']);

                                        // Insert Detail

                                        $dm = $db->prepare("Insert into detail_table (master_no,master_type,shipment_no,enter_timestamp,peices,weight,delivery_status_code,update_timestamp,received_by) Values (?,?,?,?,?,?,?,?,?)");

                                        $im = $dm->execute([$id,'DV',$shipment_no, $current_date,'1','2','33',$current_date,$param['consignee_name']]);
                                        
                                         if($im){
                                             $this->logger($shipment_no,'Ready To Return Inserted!','-','log','MX');
                                        }

                                        // Update Shipment

                                        // $sql = "Update shipments set current_status = 1 WHERE shipment_no='$shipment_no' AND tpcnno ='$tpcnno'";

                                        // $r = $db->prepare($sql)->execute($updata);

                                         $del_status = [
                                            'new_delivery_status' => 33,
                                        ];

                                        $del_up =  "UPDATE shipments SET delivery_status=:new_delivery_status WHERE shipment_no = '$shipment_no' AND tpcnno ='$tpcnno'";

                                       $d = $db->prepare($del_up)->execute($del_status);



                                        $success_array[] = array(

                                            'record_row'    => $record_count,

                                            'message'       => 'Status Updated to Ready To Return',

                                        );

                                    }else{

                                        $error_array[] = array(

                                            'record_row'   => $record_count,

                                            'message'  => 'Shipment Number Not Found',

                                        );

                                    }

                                }
                                
                                else if(in_array($param['status_code'],$failed_attempt)){

                                    $tpcnno = $param['cn_number'];

                                    // Get Shipment No

                                    $sql = $db->prepare("Select shipment_no,tpcnno,tpcode From shipments WHERE tpcode ='MX' and  shipment_no not in  

                                    (SELECT shipment_no from detail_table where master_type='DV' AND delivery_status_code = 1) and tpcnno ='$tpcnno' ");

                                    $sql->execute();

                                    $result = $sql->fetch(PDO::FETCH_ASSOC);

                                    if($result){

                                        $shipment_no = $result['shipment_no'];

                                        $this->logger($shipment_no,'Failed Attempt Matched!','-','log','MX');

                                        // Get Max Number

                                        $query = $db->prepare("Select ifnull(max(sheet_number),0)+1 as no From delivery_master");

                                        $query->execute();

                                        $row = $query->fetch(PDO::FETCH_ASSOC);

                                        $id = $row['no'];

                                        // Insert Delivery Master

                                        $current_date = date("Y-m-d H:i:s");

                                        $dm = $db->prepare("Insert Into delivery_master(sheet_number, sheet_date, rider_code, route_code, create_by,city_code) Values (?,?,?,?,?,?)");

                                        $rm = $dm->execute([$id,$current_date,'1', '1','CRON','1']);
                                        
                                        

                                        // Insert Detail

                                        $dm = $db->prepare("Insert into detail_table (master_no,master_type,shipment_no,enter_timestamp,peices,weight,delivery_status_code,update_timestamp,received_by) Values (?,?,?,?,?,?,?,?,?)");

                                        $im = $dm->execute([$id,'DV',$shipment_no, $current_date,'1','2','35',$current_date,$param['consignee_name']]);
                                        
                                         if($im){
                                             $this->logger($shipment_no,'Failed Attempt Inserted!','-','log','MX');
                                        }

                                        // Update Shipment

                                        // $sql = "Update shipments set current_status = 1 WHERE shipment_no='$shipment_no' AND tpcnno ='$tpcnno'";

                                        // $r = $db->prepare($sql)->execute($updata);

                                        $del_status = [
                                            'new_delivery_status' => 35,
                                        ];

                                        $del_up =  "UPDATE shipments SET delivery_status=:new_delivery_status WHERE shipment_no = '$shipment_no' AND tpcnno ='$tpcnno'";

                                       $d = $db->prepare($del_up)->execute($del_status);

                                        $success_array[] = array(

                                            'record_row'    => $record_count,

                                            'message'       => 'Status Updated to Failed Attempt',

                                        );

                                    }else{

                                        $error_array[] = array(

                                            'record_row'   => $record_count,

                                            'message'  => 'Shipment Number Not Found',

                                        );

                                    }

                                }

                                $record_count++;

                            }

                            $res = array(

                                "response" =>true,

                                "message" => array( "success" => $success_array,"error"=>$error_array),

                            );

                            $this->logger('true',$res,'-','log','MX');

                        }else{

                            $res = array(

                                "response" =>false,

                                "message" =>"Unable to Push Status, Data is empty."

                            );

                            $this->logger('false',$res,'Unable to Push Status, Data is empty.','log','MX');

                        }

                    }else{

                        $res = array(

                            "response" =>false,

                            "message" =>"Invalid API Key"

                        );

                        $this->logger('false',$res,'Invalid API Key','log','MX');

                    }

                    $this->logger('true','Cron Closed','-','log','MX');

                    return json_encode($res);

                    $database->closeConnection();

                }

                catch (PDOException $e)

                {

                    header('HTTP/1.1 401 Unauthorized');

                    http_response_code(401);

                    http_response_code();

                    $error = array(

                        "message" =>"Bad/Invalid or Unprocessable Request"

                    );

                    $tok = array(

                        "status" => 0,

                        "errors" => json_decode($error)

                    );

                    return $tok;

                    

                }

            break;
            case "pickups":
                try
                {
                    $database = new Connection();
                    $db = $database->openConnection();
                    $requestHeaders = apache_request_headers();
                    $auth = $requestHeaders['Authorization'];
                    if($auth){
                        $getaccount = $db->prepare("SELECT account_number FROM `customer_account` WHERE api_key='$auth'");
                        $getaccount->execute();
                        $row = $getaccount->fetch(PDO::FETCH_ASSOC);
                        $account_number = $row['account_number'];
                        $result = array();
                        $pickup_information = "SELECT * FROM pickup_information where account_number = '$account_number' ";
                        foreach ($db->query($pickup_information) as $row) {
                            $data = array(
                                "pickup_code"        =>      $row['pickup_code'],
                                "account_number"      =>      $row['account_number'],
                                "pickup_name"           =>      $row['pickup_name'],
                                "pickup_address"         =>      $row['pickup_address'],
                                "pickup_contact"         =>      $row['pickup_contact'],
                                "pickup_email"         =>      $row['pickup_email'],
                                "city_code"           =>      $row['city_code'],
                                "create_timestamp"            =>      $row['create_timestamp']
                            );
                            array_push($result,$data);
                        }
                        $res = array(
                            "response" => 200,
                            "body" =>$result,
                            "message" =>""
                        );
                    }else{
                        $res = array(
                            "response" =>404,
                            "body" =>"",
                            "message" =>"API Token (Authorization) is Missing"
                        );
                    }
                    return json_encode($res);
                    $database->closeConnection();
                }
                catch (PDOException $e)
                {
                    return "There is some problem in connection: " . $e->getMessage();
                    
                }
            break;
            case "city_list":
                try
                {
                    $database = new Connection();
                    $db = $database->openConnection();
                    $requestHeaders = apache_request_headers();
                    $auth = $requestHeaders['Authorization'];
                    if($auth){
                        $getaccount = $db->prepare("SELECT account_number FROM `customer_account` WHERE api_key='$auth'");
                        $getaccount->execute();
                        $row = $getaccount->fetch(PDO::FETCH_ASSOC);
                        $account_number = $row['account_number'];
                        $result = array();
                        $get_cities = "Select city_code, city_name, city_id From master_city_list Order by city_id";
                        foreach ($db->query($get_cities) as $row) {
                            $data = array(
                                "city_code"        =>      $row['city_code'],
                                "city_name"      =>      $row['city_name']
                            );
                            array_push($result,$data);
                        }
                        // echo "<pre>";print_r($result);die;
                        $res = array(
                            "response" => 200,
                            "body" =>$result,
                            "message" =>""
                        );
                    }else{
                        $res = array(
                            "response" =>404,
                            "body" =>"",
                            "message" =>"API Token (Authorization) is Missing"
                        );
                    }
                    return json_encode($res);
                    $database->closeConnection();
                }
                catch (PDOException $e)
                {
                    return "There is some problem in connection: " . $e->getMessage();
                    
                }
            break;
            case "book":
                try
                {
                    $database = new Connection();
                    $db = $database->openConnection();
                    $requestHeaders = apache_request_headers();
                    $auth = $requestHeaders['Authorization'];
                    if($auth){
                        date_default_timezone_set('Asia/Karachi');
                        $sql = $db->prepare("Select account_number From customer_account Where api_key = '{$auth}'");
                        $sql->execute();
                        $result = $sql->fetch(PDO::FETCH_ASSOC);
                        if($result){
                            $acc_no = $result['account_number'];
                            $pickup_code = $params['PickupCode'];
                            $shipmentdate = date('Y-m-d H:i:s');
                            $account_number = $acc_no;
                            $consigneeName = $params["ConsigneeName"];
                            $consigneeAddress = $params["ConsigneeAddress"];
                            $consigneeEmail = $params["ConsigneeEmail"];
                            $consigneeContact = $params["ConsigneeContact"];
                            $shipmentReference = $params["ShipmentReference"];
                            $serviceCode = $params["ServiceCode"];
                            $parceltype = $params["ParcelType"];
                            $destinationCity = $params["DestinationCity"];
                            $pieces = $params["Pieces"];
                            $weight = $params["Weight"];
                            $fragileRequired = $params["FragileRequired"];
                            $insuranceRequired = $params["InsuranceRequired"];
                            $cashCollectRequired = $params["CashCollectRequired"];
                            $shipperComment = $params["ShipperComment"];
                            $COD = $params["COD"];
                            $cashCollect = $params["CashCollect"];
                            $productDetail = $params["ProductDetail"];
                            // Get Origin City 
                            $getOrigin = $db->prepare("Select city_code From pickup_information Where pickup_code = {$pickup_code}");
                            $getOrigin->execute();
                            $fetchOrigin = $getOrigin->fetch(PDO::FETCH_ASSOC);
                                if($fetchOrigin){
                                    $origin_city = $fetchOrigin['city_code'];
                                }else{
                                    $response = array(
                                        'response' => 404,
                                        'message'=> 'Invalid PickupCode',
                                    );
                                }
                                switch ($params) {
                                    case ($params['PickupCode']==''):
                                        $response = array(
                                        'response' => 404,
                                        'message'=> 'Missing PickupCode',
                                    );
                                    break;
                                    case ($params['ConsigneeName'] == ''):
                                    $response = array(
                                        'response' => 404,
                                        'message'=> 'Missing Consignee Name',
                                    );
                                    break;
                                    case ($params['ConsigneeAddress'] == ''):
                                    $response = array(
                                        'response' => 404,
                                         'message'=> 'Missing Consignee Address',
                                    );
                                    break;
                                    case ($params['DestinationCity'] == ''):
                                    $response = array(
                                        'response' => 404,
                                         'message'=> 'Missing Destination City',
                                    );
                                    break;
                                    case ($params['ConsigneeContact'] == ''):
                                    $response = array(
                                        'response' => 404,
                                        'message'=> 'Missing Consignee Contact',
                                    );
                                    break;
                                    case ($params['Pieces'] == '' || $params['Pieces'] == 0):
                                    $response = array(
                                        'response' => 404,
                                        'message'=> 'Missing Quantity',
                                    );
                                    break;
                                    case ($params['Weight'] == '' || $params['Weight'] == 0):
                                    $response = array(
                                        'response' => 404,
                                        'message'=> 'Missing Weight',
                                    );
                                    break;
                                    case ($params['COD'] == 'Yes' && ($params["CashCollect"] == 0 || $params["CashCollect"] == '' || $params["CashCollect"] == null)):
                                    $response = array(
                                        'response' => 404,
                                        'message'=> 'Missing Cod Collection',
                                    );
                                    break;
                                    case ($params['ProductDetail'] == '' || empty($params['ProductDetail'])):
                                    $response = array(
                                        'response' => 404,
                                        'message'=> 'Missing Product Detail',
                                    );
                                    break;
                                }
                                if(isset($response)){
                                    return json_encode($response);
                                    exit();
                                }
                                // Get Username
                                $getusername = $db->prepare("Select username From user_information Where account_number = {$acc_no}");
                                $getusername->execute();
                                $fetchUsername = $getusername->fetch(PDO::FETCH_ASSOC);
                                $username = $fetchUsername['username'];
                                // Create Shipment No
                                $shipment_no = mt_rand(200000000, 299999999);
                                $i=0;
                                while($i<1){
                                    $getshipmentno = "Select count(*) as cc From shipments Where shipment_no = {$shipment_no}"; 
                                    $getshipmentno = $db->query($getshipmentno);
                                    $shipment_nos = $getshipmentno->fetch(PDO::FETCH_LAZY);
                                    $shipment_nos = $shipment_nos['cc'];
                                    if($shipment_nos==0){
                                        $i=1;
                                    }else{
                                        $i=0;
                                    }
                                }
                                // JSON Log
                                $json = array(
                                    "shipment_no" =>$shipment_no,
                                    "shipment_date" =>$shipmentdate,
                                    "account_number" =>$acc_no,
                                    "pickup_code" =>$pickup_code,
                                    "consignee_name"=>$consigneeName,
                                    "consignee_address"=>$consigneeAddress,
                                    "consignee_email"=>$consigneeEmail,
                                    "consignee_contact"=>$consigneeContact,
                                    "shipment_reference"=>$shipmentReference,
                                    "service_code"=>$serviceCode,
                                    "parcel_type"=>$parceltype,
                                    "peices"=>$pieces,
                                    "weight"=>$weight,
                                    "cash_collect_required"=>$cashCollectRequired,
                                    "shipper_comment"=>$shipperComment,
                                    "cash_collect"=>$cashCollect,
                                    "product_detail"=>$productDetail,
                                    "origin_country"=>'PK',
                                    "origin_city" =>$origin_city,
                                    "destination_country"=>'PK',
                                    "destination_city"=>$destinationCity,
                                    "created_by"=>$username,
                                );
                                $json_data = json_encode($json);
                                $bookShipment = $db->prepare("Insert Into shipments (shipment_no, shipment_date, account_number, pickup_code,consignee_name, 
                                    consignee_address, consignee_email, consignee_contact, shipment_reference,service_code, parcel_type, peices, weight, fagile_required, 
                                    insurance_required, cash_collect_required, shipper_comment, cash_collect, product_detail, origin_country,origin_city, 
                                    destination_country, destination_city, created_by, current_status,json_data)
                                Values(?,?, ?, ?,?,?, ?, ?,?, ?, ?, ?, ?,?, ?, ?, ?, ?, ?,?, ?, ?, ?,?, ?,?);");
                                 $booked = $bookShipment->execute([
                                    $shipment_no,$shipmentdate, $acc_no, $pickup_code, 
                                    $consigneeName,$consigneeAddress,$consigneeEmail, $consigneeContact, 
                                    $shipmentReference,$serviceCode, $parceltype, $pieces, $weight, 
                                    $fragileRequired,$insuranceRequired, $cashCollectRequired, $shipperComment, 
                                    $cashCollect, $productDetail, 'PK',$origin_city, 'PK', $destinationCity, $username, 0, $json_data
                                ]);
                                if($booked){

                                    if($weight<= "0.5" && $destinationCity!="KHI"){

                         
                                if($_SESSION['meta_data'][0]['address']!='' || $_SESSION['meta_data'][0]['address']!=null){
                                $shipper_info = json_decode($_SESSION['meta_data'][0]['address']);
                                $shipper_name = $shipper_info->shipper_name;
                                $shipper_email = $shipper_info->shipper_email;
                                $shipper_contact = $shipper_info->shipper_contact;
                                $shipper_address = $shipper_info->shipper_address;
                                $shipper_origin_code = $shipper_info->shipper_origin_code;
                            }else{
                                    $shipper_name = 'self';
                                    $shipper_email = 'self';
                                    $shipper_contact = 'self';
                                    $shipper_address = 'self';  
                                    $shipper_origin_code ='self';     
                                }
                                        // echo "test";die;

                                    $cities = $db->prepare("SELECT origin.city_code_leo as org,des.city_code_leo as des
                                FROM
                                    master_city_list m
                                    INNER JOIN master_city_list origin on  origin.city_code = '$origin_city'
                                    INNER JOIN master_city_list des on  des.city_code = '$destinationCity' 
                                    group by origin.city_code_leo,des.city_code_leo");
                                   $cities->execute();
                            $city_leo = $cities->fetch(PDO::FETCH_ASSOC);
                        $api_key = '3EC39671058F6B18282591F22541D570';
                        $api_password = '3EC39671058F6B182825';
                        $header = array();
                        $header[] = 'Content-type: application/json';
                        $header[] = 'Authorization:'.$api_key;
                        $url="http://new.leopardscod.com/webservice/bookPacket/format/json/";
                        $json = array(
                            'api_key'                       => $api_key,
                            'api_password'                  => $api_password,
                            'booked_packet_weight'          => $weight,                   
                            'booked_packet_no_piece'        => $pieces,                 
                            'booked_packet_collect_amount'  => $cashCollect,                 
                            'booked_packet_order_id'        => $shipment_no,            
                            'origin_city'                   => $city_leo['org'],  //to be get from rehan lepoard    
                            'destination_city'              => $city_leo['des'],            
                            'shipment_name_eng'             => $shipper_name,            
                            'shipment_email'                => $shipper_email,           
                            'shipment_phone'                => $shipper_contact,            
                            'shipment_address'              => $shipper_address,           
                            'consignment_name_eng'          => $consigneeName,            
                            'consignment_email'             => $consigneeEmail,         
                            'consignment_phone'             => $consigneeContact,            
                            'consignment_phone_two'         => "",           
                            'consignment_phone_three'       => "",            
                            'consignment_address'           => $consigneeAddress,           
                            'special_instructions'          => $shipperComment,            
                            'shipment_type'                 => 'overnight'
                        ); 
                        // echo "<pre>";print_r($json);die;
                        $curl = curl_init();
                        curl_setopt_array($curl, array(
                            CURLOPT_URL => $url,
                            CURLOPT_RETURNTRANSFER => true,
                            CURLOPT_ENCODING => "",
                            CURLOPT_MAXREDIRS => 10,
                            CURLOPT_TIMEOUT => 0,
                            CURLOPT_FOLLOWLOCATION => true,
                            CURLOPT_SSL_VERIFYHOST => false,
                            CURLOPT_SSL_VERIFYPEER => false,
                            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                            CURLOPT_CUSTOMREQUEST => "POST",
                            CURLOPT_POSTFIELDS =>json_encode($json),
                            CURLOPT_HTTPHEADER => $header
                          ));

                          $response     = curl_exec($curl);
                          // echo "<pre>";print_r($response);die;

                          $result       = json_decode($response);
                          $json_data = json_encode($json);

                        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
                        $error_msg = curl_error($curl);
                        curl_close($curl);
                        $res_msg = $city_leo."-".$error_msg."-".$response;
                          $st = $result->status;
                        $tpcnno = $result->track_number;

                            if($st==1){
                                $updata = [
                                    'tpcnno' => $tpcnno,
                                    'tpres' => $res_msg,
                                    'tpcode' => 'L',
                                    'tdata' => $json_data
                                ];
                                $sql = "UPDATE shipments SET tpcnno=:tpcnno,tpres=:tpres,tpcode=:tpcode,tdata=:tdata WHERE  shipment_no = '{$shipment_no}'";
                                $r = $db->prepare($sql)->execute($updata);
                            }else{
                                $updata = [
                                    'tpres' => $res_msg,
                                    'tdata' => $json_data
                                ];
                                $sql = "UPDATE shipments SET tpres=:tpres,tdata=:tdata WHERE  shipment_no = '{$shipment_no}'";
                                $r = $db->prepare($sql)->execute($updata);
                            }
                        }
                     }
                                 
                                if(date('H')>15){
                                    $pickup_date = date('Y-m-d 00:00:00', strtotime("+1 day"));
                                }else{
                                    $pickup_date = $shipmentdate;
                                }
                                // Get PickUps
                                $getPickup = $db->prepare("Select count(*) as p From pickups Where account_number = '$acc_no' and pickup_code = '$pickup_code' 
                                and pickup_date = '$pickup_date' and city_code = '$origin_city'");
                                $getPickup->execute();
                                $fetchPickup = $getPickup->fetch(PDO::FETCH_ASSOC);
                                if($fetchPickup['p']==0){
                                    $AddPickup = $db->prepare("Insert Into pickups(pickup_date, city_code,account_number, pickup_code, pickup_status, create_timestamp)
                                    Values(?, ?, ?, ?, ?, ?);");
                                    $picked = $AddPickup->execute([
                                     $pickup_date,$origin_city, $acc_no, $pickup_code, 0,$shipmentdate
                                    ]);  
                                }
                                $response = array(
                                    'response' => 200,
                                    "shipment_no" => $shipment_no,
                                    "message"     => "Shipment has been Booked",
                                );
                            $res = $response;
                        }else{
                            $res = array(
                                'response' => 404,
                                "message" =>"Invalid API Key"
                            );
                        }
                    }else{
                        $res = array(
                            'response' => 404,
                            "message" =>"API Token (Authorization) is Missing"
                        );
                    }
                    return json_encode($res);
                    $database->closeConnection();
                  
                }
                catch (PDOException $e)
                {
                    return "Something went wrong !";
                    
                }
            break;
            
             case "track":
                try
                {
                    $database = new Connection();
                    $db = $database->openConnection();
                    $requestHeaders = apache_request_headers();
                    $auth = $requestHeaders['Authorization'];
                    if($auth){
                        if(!empty($params)){
                            $sql = $db->prepare("Select account_number From customer_account Where api_key = '{$auth}'");
                            $sql->execute();
                            $result = $sql->fetch(PDO::FETCH_ASSOC);
                            if($result){
                                $params = explode(",",$params);
                                $account_number = $result['account_number'];
                                $trackingArr =array();
                                foreach($params as $param){
                                    $getorderData = $db->prepare("SELECT shipment_no,shipment_date,account_number,consignee_name,consignee_email,consignee_contact,consignee_address,weight,peices,cash_collect_required,cash_collect,shipment_reference,origin_city,destination_city FROM shipments  WHERE shipment_no = '$param'");
                                    $getorderData->execute();
                                    $orderDetails = $getorderData->fetch(PDO::FETCH_ASSOC);
                                    if (!empty($orderDetails)) {
                                        if($orderDetails['account_number']!=$account_number){
                                            $error_array[]= array(
                                                'shipment_no' => $param, 
                                                'message' => 'Shipment Not Belongs To User', 
                                              );
                                        }
                                    }else{
                                        $error_array[] = array(
                                           'shipment_no' => $param, 
                                            'message' =>'Shipment Number Not Found', 
                                        );
                                    }                                    
                                }
                                if (count($error_array)==0) {
                                    foreach($params as $param){
                                        $getorderData = $db->prepare("SELECT shipment_no,shipment_date,account_number,consignee_name,consignee_email,consignee_contact,consignee_address,weight,peices,cash_collect_required,cash_collect,shipment_reference,origin_city,destination_city FROM shipments  WHERE shipment_no = '$param' AND account_number='$account_number'");
                                        $getorderData->execute();
                                        $orderDetails = $getorderData->fetch(PDO::FETCH_ASSOC);
                                        $shipment_no = $orderDetails['shipment_no'];
                                        $getTrackingData = $db->prepare("SELECT dt.shipment_no,dt.enter_timestamp as created_datetime,dt.received_by,(case when dt.delivery_status_code is null then dt.master_type else st.delivery_status_detail end) as shipment_status FROM detail_table dt LEFT JOIN delivery_status st on st.delivery_status_code = dt.delivery_status_code WHERE dt.shipment_no ='$shipment_no' Order by dt.enter_timestamp ASC");
                                        $getTrackingData->execute();
                                        $trackingDetails = $getTrackingData->fetchAll(PDO::FETCH_ASSOC);
                                        if($trackingDetails){
                                            $orderDetails['tracking_info'] = $trackingDetails;
                                        }else{
                                            $orderDetails['tracking_info'] = array();
                                        }
                                        array_push($trackingArr, $orderDetails);
                                    }
                                    $res = array(
                                        'response' => 200,
                                        "message" => "Shipment Tracking History",
                                        "tracking_details" => $trackingArr
                                    );
                                }else{
                                    $res = array(
                                        'response' => 404,
                                        'message'  => 'Error Occured',
                                        'tracking_details' => array('error'=>$error_array),
                                    );
                                }
                            }else{
                                $res = array(
                                    'response' => 404,
                                    "message" =>"Invalid API Key"
                                );
                            }
                        }else{
                            $res = array(
                                'response' => 404,
                                'message'  => 'Missing Parameter',
                            );
                        }
                    }else{
                        $res = array(
                            'response' => 404,
                            "message" =>"Missing API Key"
                        );
                    }
                    return json_encode($res);
                    $database->closeConnection();
                }
                catch (PDOException $e)
                {
                    return "Something went wrong !";
                    
                }
            break;
            
            
           case "address-label":
                try{
                    $database = new Connection();
                    $db = $database->openConnection();
                    $requestHeaders = apache_request_headers();
                    $auth = $requestHeaders['Authorization'];
                    if($auth){
                        $getaccount = $db->prepare("SELECT account_number FROM `customer_account` WHERE api_key='$auth'");
                        $getaccount->execute();
                        $row = $getaccount->fetch(PDO::FETCH_ASSOC);
                        $account_number = $row['account_number'];
                        $result = array();
                        $cns = implode(',',$params);
                        $sql = "Select shipment_no, date_format(shipment_date, '%d/%m/%Y') as shipment_date, shipments.account_number,
                           account_name as shipper_name, account_cell as shipper_cell, account_address as shipper_address,
                           consignee_name, consignee_address, consignee_email, consignee_contact, shipment_reference, service_name,
                           parcel_type, peices, weight, fagile_required, origin_country, origin_city,
                           mc.city_code_leo thirdparty_city,
                           destination_country, destination_city, case cash_collect_required when 1 then cash_collect when 0 then 0 end as cash_collect, shipper_comment, product_detail, tpcnno
                           From shipments
                           Inner Join customer_account on shipments.account_number = customer_account.account_number
                           Left Join services on shipments.service_code = services.service_code
                           Left Join display_information on shipments.account_number = display_information.account_number
                           Left Join master_city_list as mc on mc.city_code = shipments.destination_city
                           Where shipment_no in ({$cns}) " ;
                       $data_arr = array();
                        foreach ($db->query($sql) as $row) {
                          $data = array(
                              "shipment_no" => $row['shipment_no'],
                              "shipment_date" => $row['shipment_date'],
                              "account_number" => $row['account_number'],
                              "shipper_name" => $row['shipper_name'],
                              "shipper_cell" => $row['shipper_cell'],
                              "shipper_address" => $row['shipper_address'],
                              "consignee_name" => $row['consignee_name'],
                              "consignee_address" => $row['consignee_address'],
                              "consignee_email" => $row['consignee_email'],
                              "consignee_contact" => $row['consignee_contact'],
                              "shipment_reference" => $row['shipment_reference'],
                              "service_name" => $row['service_name'],
                              "parcel_type" => $row['parcel_type'],
                              "peices" => $row['peices'],
                              "weight" => $row['weight'],
                              "fagile_required" => $row['fagile_required'],
                              "origin_country" => $row['origin_country'],
                              "origin_city" => $row['origin_city'],
                              "thirdparty_city" => $row['thirdparty_city'],
                              "destination_country" => $row['destination_country'],
                              "destination_city" => $row['destination_city'],
                              "cash_collect" => $row['cash_collect'],
                              "shipper_comment" => $row['shipper_comment'],
                              "product_detail" => $row['product_detail'],
                              "tpcnno" => $row['tpcnno'],
                          );
                          array_push($data_arr,$data);
                        }
                        $res = array(
            
                            "response" => 200,
            
                            "body" =>$data_arr,
            
                            "message" =>""
            
                        );
            
                
                   }else{
            
                        $res = array(
            
                            "response" =>404,
            
                            "body" =>"",
            
                            "message" =>"API Token (Authorization) is Missing"
            
                        );
                    }
                    return json_encode($res);
                    $database->closeConnection();
                }
                catch (PDOException $e){
                    return "There is some problem in connection: " . $e->getMessage();
                }
            break;
            
            case "cancel":
                try
                {
                    $database = new Connection();
                    $db = $database->openConnection();
                    $requestHeaders = apache_request_headers();
                    $auth = $requestHeaders['Authorization'];
                    if($auth){
                        $sql = $db->prepare("Select account_number From customer_account Where api_key = '{$auth}'");
                        $sql->execute();
                        $result = $sql->fetch(PDO::FETCH_ASSOC);
                        if($result){
                            $param = $params['shipment_no'];
                            $account_number = $result['account_number'];
                            $Q = $db->prepare("SELECT COUNT(shipment_no) as count from customer_arrival_not_done where shipment_no = '{$param}' and account_number = '{$account_number}'");
                            $Q->execute();
                            $r = $Q->fetch(PDO::FETCH_ASSOC);
                            if($r['count']==1){
                                $updata = [
                                    'new_current_status' => 2,
                                    'old_current_status' => 0,
                                ];
                                $sql = "UPDATE shipments SET current_status=:new_current_status WHERE current_status=:old_current_status and shipment_no = '{$param}'";
                                $r = $db->prepare($sql)->execute($updata);
                                if($r){
                                    $response = "200";
                                    $message = "Booking has been Cancel";
                                }
                            }else{
                                $response = "404";
                                $message = "Shipment No is not eligible to cancel";
                            }
                            $res = array(
                                "response" => $response,
                                "shipment_no" => $param,
                                "message" => $message
                            );
                        }else{
                            $res = array(
                                "message" =>"Invalid API Key"
                            );
                        }
                    }else{
                        $res = array(
                            "message" =>"API Token (Authorization) is Missing"
                        );
                    }
                    return json_encode($res);
                    $database->closeConnection();
                }
                catch (PDOException $e)
                {
                    return "Something went wrong !";
                    
                }
                break;
            default:
                return "Something went wrong !";
        }
    }
     public function logger($status,$mode,$log,$case,$from){

        switch ($case){

            case "log":

                try{

                    $database = new Connection();

                    $db = $database->openConnection();

                    date_default_timezone_set("Asia/Karachi");

                    $ip = $_SERVER['REMOTE_ADDR'];

                    $date = date('Y-m-d h:i:s A');

                    $contents = array(

                        "status"=>$status,

                        "message"=>$mode,

                        "ip"=>$ip

                    );
                    // print_r($from);die;
                    if($from=='Leo'){
                        $table="cron_leo";
                    }elseif($from=='MX'){
                        $table="cron_mx";
                    }

                    $contents = json_encode($contents);
                    $dm = $db->prepare('Insert into '.$table.' (meta_key,meta_value,exec_time) values (?,?,?)');
                    $rm = $dm->execute([$contents,$log,$date]);

                    $database->closeConnection();

                }

                catch (PDOException $e)

                {
                    return "There is some problem in connection: " . $e->getMessage();
                }

            break;

            default:

            return "Something went wrong !";
        }

    }

    public function json_logs($data){
        $url="https://flycourier.orio.digital/dev/api/logs_data.php";
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS =>$data,
         ));
        $response     = curl_exec($curl);
        return $response;
    }
}
    
