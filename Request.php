<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// require 'vendor/autoload.php';
include_once 'IRequest.php';

use phpseclib3\Net\SFTP;
use phpseclib3\Net\SSH2;
define('NET_SSH2_LOG_REALTIME_FILENAME', 'vendor/log.txt');

date_default_timezone_set("America/New_York");
// require_once ('../ops/library/tcpdf_min/tcpdf.php');


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
            case "addcaregivers":
                return $this->verify_action('POST', $req, $case);
            break;
            case "getcaregivers":
                return $this->verify_action('POST', $req, $case);
            break;
            case "postvisit":
                return $this->verify_action('POST', $req, $case);
            break;
            case "endchat":
                return $this->verify_action('POST', $req, $case);
            break;
            case "getleads":
                return $this->verify_action('POST', $req, $case);
            break;
            case "sendmsgadmin":
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
            case "addcaregivers":
                return $this->get_data('SELECT', $params, $case);
            break;
            case "getcaregivers":
                return $this->get_data('SELECT', $params, $case);
            break;
            case "postvisit":
                return $this->get_data('SELECT', $params, $case);
            break;
            case "endchat":
                return $this->get_data('SELECT', $params, $case);
            break;
            case "getleads":
                return $this->get_data('SELECT', $params, $case);
            break;
            case "sendmsgadmin":
                return $this->get_data('SELECT', $params, $case);
            break;
            default:
                return "Something went wrong !";
        }
    }

    public function get_data($method, $params, $case)
    {
        // $GLOBALS['auth'] = $this->auth();
        // if ($GLOBALS['auth']['host'] == $_SERVER['HTTP_ORIGIN'])
        // {
            $add_params = '';
            switch ($case)
            {
                case "auth":

                    // if (!empty($params['user_id']))
                    // {

                    //     $database = new Connection();
                    //     $incoming_id = $params['user_id'];
                    //     $lead_id = $params['lead_id'];
                    //     $db = $database->openConnection();
                    //     $query = $db->prepare("select * from users where id='$incoming_id'");
                    //     $query->execute();
                    //     $count = $query->rowCount();
                    //     if ($query->rowCount() > 0)
                    //     {
                    //         // $row = $query->fetch(PDO::FETCH_ASSOC);
                    //         // $id = $row['id'];
                    //         $query = $db->prepare("SELECT lh.*,a.name,l.status as chat_end FROM leads l left join lead_history lh on lh.lead_id = l.id left join admins a on a.id = lh.responder_id WHERE l.uid ='$incoming_id' and l.id='$lead_id' and l.status!=0 order by lh.id");
                    //         $query->execute();
                    //         $count = $query->rowCount();
                    //         $rows = [];
                    //         if ($query->rowCount() > 0)
                    //         {
                    //             while ($row = $query->fetch(PDO::FETCH_ASSOC))
                    //             {
                    //                 $rows[] = $row;
                    //             }
                    //             $res = array(
                    //                 'result' => 'success',
                    //                 'data' => $rows
                    //             );
                    //         }
                    //         else
                    //         {
                    //             $res = array(
                    //                 'result' => 'success',
                    //                 'message' => 'lead closed'
                    //             );
                    //         }

                    //         //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                    //         return json_encode($res);

                    //     }
                    //     else
                    //     {

                    //         $msg = "error getting message !";
                    //         $res = array(
                    //             'result' => 'error',
                    //             'message' => $msg
                    //         );
                    //         //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                    //         return json_encode($res);
                    //     }

                    // }
                    if($params['user_id']=='demo' && $params['pwd']=='12345')
                    {
                        $msg = "Authenticated";
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }
                    else
                    {
                        $msg = "invalid password or username";
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }

                break;
                case "addcaregivers":
                        $database = new Connection();
                        $db = $database->openConnection();
                        $table = 'caregivers';
                        
                        $contents = json_encode($params);
                        $dm = $db->prepare('Insert into ' . $table . ' (meta_key,meta_value,endpoint) values (?,?,?)');
                        $rm = $dm->execute(['caregivers', $contents , 'addcaregivers']);
                        $database->closeConnection();
                        $msg = 'caregiver posted';
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                            );
                        return json_encode($res);
                break;
                
                case "postvisit":
                        

                       
                        ini_set('display_errors', 1);
                        ini_set('display_startup_errors', 1);
                        error_reporting(E_ALL);
                        // $database = new Connection();
                        // $db = $database->openConnection();
                        // $table = 'caregivers';
                        
                        // $contents = json_encode($params);
                        // $dm = $db->prepare('Insert into ' . $table . ' (meta_key,meta_value,endpoint) values (?,?,?)');
                        // $rm = $dm->execute(['caregivers', $contents , 'addcaregivers']);
                        // $database->closeConnection();
                        
                        
                        $rows = [
                            ["Agency Tax ID",
                            "Office NPI",
                            "Payer ID",
                            "Medicaid Number",
                            "Member First Name",
                            "Member Last Name",
                            "Member ID",
                            "Caregiver Code",
                            "Caregiver Registry ID",
                            "Caregiver License Number",
                            "Caregiver First Name",
                            "Caregiver Last Name",
                            "Caregiver Gender",
                            "Caregiver Date of Birth",
                            "Caregiver SSN",
                            "Caregiver Email",
                            "Schedule ID",
                            "Visit ID",
                            "Procedure Code",
                            "Diagnosis Code",
                            "Schedule Start Time",
                            "Schedule End Time",
                            "Visit Start Time",
                            "Visit End Time",
                            "EVV Start Time",
                            "EVV End Time",
                            "Clock-In Service Location Address Line 1",
                            "Clock-In Service Location Address Line 2",
                            "Clock-In Service Location City",
                            "Clock-In Service Location State ",
                            "Clock-In Service Location Zip Code",
                            "Clock-In Service Location Type",
                            "Clock-Out Service Location Address Line 1",
                            "Clock-Out Service Location Address Line 2",
                            "Clock-Out Service Location City",
                            "Clock-Out Service Location State ",
                            "Clock-Out Service Location Zip Code",
                            "Clock-Out Service Location Type",
                            "Duties",
                            "Clock-In Phone Number",
                            "Clock-In Latitude",
                            "Clock-In Longitude",
                            "Clock-In EVV Other Info",
                            "Clock-Out Phone Number",
                            "Clock-Out Latitude",
                            "Clock-Out Longitude",
                            "Clock-Out EVV Other Info",
                            "Invoice Number",
                            "Visit Edit Reason Code",
                            "Visit Edit Action Taken",
                            "Visit Edit Made By",
                            "Notes",
                            "Is Deletion",
                            "Invoice Line-Item ID",
                            "Total Billed Amount",
                            "Units Billed",
                            "Billed Rate",
                            "Submission Type",
                            "TRN Number",
                            "Enable Secondary Billing",
                            "Other Subscriber ID",
                            "Primary Payer ID",
                            "Primary Payer Name",
                            "Relationship to Insured",
                            "Primary Payer Policy or Group number",
                            "Primary Payer Program Name",
                            "Plan Type",
                            "Total Paid Amount",
                            "Total Paid Units",
                            "Paid Date",
                            "Deductible",
                            "Coinsurance",
                            "Copay",
                            "Contracted Adjustments",
                            "Not Medically Necessary",
                            "Non-Covered Charges",
                            "Max Benefit Exhausted",
                            "Missed Visit",
                            "Missed Visit Reason Code",
                            "Missed Visit Action Taken Code",
                            "Missed Visit Notes",
                            "Travel Time Request Hours",
                            "Travel Time Comments",
                            "Cancel Travel Time Request",
                            "Timesheet Required",
                            "Timesheet Approved",
                            "User Field 1",
                            "User Field 2",
                            "User Field 3",
                            "User Field 4",
                            "User Field 5",
                            "User Field 6",
                            "User Field 7",
                            "User Field 8",
                            "User Field 9",
                            "User Field 10"
                            ],
    [181808, "Star Wars: The Last Jedi", "https://image.tmdb.org/t/p/w500/kOVEVeg59E0wsnXmF9nrh6OmWII.jpg", "Rey develops her newly discovered abilities with the guidance of Luke Skywalker, who is unsettled by the strength of her powers. Meanwhile, the Resistance prepares to do battle with the First Order.", 1513123200, "Documentary"],
    [383498, "Deadpool 2", "https://image.tmdb.org/t/p/w500/to0spRl1CMDvyUbOnbb4fTk3VAd.jpg", "Wisecracking mercenary Deadpool battles the evil and powerful Cable and other bad guys to save a boy's life.", 1526346000, "Action, Comedy, Adventure"],
    [157336, "Interstellar", "https://image.tmdb.org/t/p/w500/gEU2QniE6E77NI6lCU6MxlNBvIx.jpg", "Interstellar chronicles the adventures of a group of explorers who make use of a newly discovered wormhole to surpass the limitations on human space travel and conquer the vast distances involved in an interstellar voyage.",1415145600,"Adventure, Drama, Science Fiction"]
];
$path = $_SERVER['DOCUMENT_ROOT'].'/empire/test.csv';

$fp = fopen($path, 'w'); // open in write only mode (write at the start of the file)
foreach ($rows as $row) {
    fputcsv($fp, $row);
}
fclose($fp);

// 
 $sftp = new SFTP('kristenlaw.penntelco.com',2112);

if (!$sftp->login('root', 'Galico@1214')) {
    throw new \Exception('Host key verification failed');
}
// else{
//     echo 'login good';
//     //$sftp->put('remote.ext', $path, SFTP::SOURCE_LOCAL_FILE);
    
// }

// $ssh = new SSH2('kristenlaw.penntelco.com','2112');
// if ($expected != $ssh->getServerPublicHostKey()) {
//     throw new \Exception('Host key verification failed');
// }
// print_r($ssh->getLog());

// $fsock = fsockopen('kristenlaw.penntelco.com',2112, $errno, $errstr, 1);
// if (!$fsock) {
//     throw new \Exception($errstr);
// }


// $resFile = fopen("ssh2.sftp://{$resSFTP}/".$csv_filename, 'w');
// $srcFile = fopen("/home/myusername/".$csv_filename, 'r');
// $writtenBytes = stream_copy_to_stream($srcFile, $resFile);
// fclose($resFile);
// fclose($srcFile);
                        $msg = 'Visit posted';
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                            );
                        return json_encode($res);
                break;
                case "getcaregivers":

                        $database = new Connection();
                        $db = $database->openConnection();
                        
                        $table = 'caregivers';
                        
                        $query = $db->prepare('select * from '. $table );
                        $query->execute();
                        $count = $query->rowCount();
                        $rows = [];
                        $msg =  'fetched total '.$count.' caregivers';
                        if ($query->rowCount() > 0)
                        {
                            while ($caregivers = $query->fetch(PDO::FETCH_ASSOC))
                                {
                                    $rows[] = json_decode($caregivers['meta_value']);
                                }
                            
                        }
                        $res = array(
                            'result' => 'success',
                            'message' => $msg,
                            'data'  => $rows
                            );
                        
                        return json_encode($res);

                break;
                
                case "sendmsgadmin":

                    if (!empty($params['sender_id']))
                    {

                        $database = new Connection();
                        $sender_id = $params['sender_id'];
                        $message = $params['message'];
                        $lead_id = $params['lead_id'];
                        // $responder_id = $params['responder_id'];
                        $db = $database->openConnection();
                        $query = $db->prepare("select * from admins where id='$sender_id'");
                        $query->execute();
                        $count = $query->rowCount();
                        if ($query->rowCount() > 0)
                        {

                            // $query = $db->prepare("SELECT lh.*,a.name,l.status as chat_end FROM leads l left join lead_history lh on lh.lead_id = l.id left join admins a on a.id = lh.responder_id WHERE l.uid ='$sender_id' and l.status!=0 order by lh.id");
                            // $query->execute();
                            // $count = $query->rowCount();
                            // $rows = [];
                            // if ($query->rowCount() > 0)
                            // {
                                $dm = $db->prepare("Insert Into lead_history(lead_id, message,responder_id,sender_id, status) Values (?,?,?,?,?)");
                                $rm = $dm->execute([$lead_id, $message, $sender_id, 0, '1']);
                                $res = array(
                                    'result' => 'success'
                                );
                            // }
                            // else
                            // {
                            //     $msg = "lead inactive !";
                            //     $res = array(
                            //         'result' => 'error',
                            //         'message' => $msg
                            //     );
                            // }

                            //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                            return json_encode($res);

                        }
                        else
                        {

                            $msg = "error sending message !";
                            $res = array(
                                'result' => 'error',
                                'message' => $msg
                            );
                            //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                            return json_encode($res);
                        }
                    }
                    else
                    {
                        $msg = "All input fields are required!";
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }

                break;
                
                
                case "endchat":

                    if (!empty($params['user_id']))
                    {

                        $database = new Connection();
                        $user_id = $params['user_id'];
                        $lead_id = $params['lead_id'];
                        $db = $database->openConnection();
                        $query = $db->prepare("select * from users where id='$user_id'");
                        $query->execute();
                        $count = $query->rowCount();
                        if ($query->rowCount() > 0)
                        {
                            // $row = $query->fetch(PDO::FETCH_ASSOC);
                            // $id = $row['id'];
                            $query = $db->prepare("SELECT lh.*,a.name,l.status as chat_end FROM leads l left join lead_history lh on lh.lead_id = l.id left join admins a on a.id = lh.responder_id WHERE l.uid ='$user_id' and l.status!=0 order by lh.id");
                            $query->execute();
                            $count = $query->rowCount();
                            $rows = [];
                            if ($query->rowCount() > 0)
                            {
                                $stmt = $db->prepare("UPDATE leads SET status = ? where id = ?");
                                $stmt->execute([0, $lead_id]);
                                $res = array(
                                    'result' => 'success'
                                );

                                //start working from here to get msg and render
                                
                            }
                            else
                            {
                                $stmt = $db->prepare("UPDATE leads SET status = ? where id = ?");
                                $stmt->execute([0, $lead_id]);
                                $res = array(
                                    'result' => 'success'
                                );
                            }

                            //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                            return json_encode($res);

                        }
                        else
                        {

                            $msg = "error !";
                            $res = array(
                                'result' => 'error',
                                'message' => $msg
                            );
                            //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                            return json_encode($res);
                        }
                    }
                    else
                    {
                        $msg = "All input fields are required!";
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }

                break;
                case "getleads":

                    if (!empty($params['user_id']))
                    {

                        $database = new Connection();
                        $user_id = $params['user_id'];
                        // $lead_id = $params['lead_id'];
                        $db = $database->openConnection();
                        $query = $db->prepare("select * from admins where id='$user_id'");
                        $query->execute();
                        $count = $query->rowCount();
                        if ($query->rowCount() > 0)
                        {
                            // $row = $query->fetch(PDO::FETCH_ASSOC);
                            // $id = $row['id'];
                            //start working from here.
                            $closed_leads = $db->prepare("SELECT l.id as lead_id,l.message,l.date,l.responder_id,l.uid,a.name,l.status,u.id,u.name,u.email,u.name FROM leads l left join admins a on a.id = l.responder_id left join users u on u.id = l.uid WHERE l.responder_id ='$user_id' and l.status = 0 order by l.id desc");
                            $closed_leads->execute();
                            $closed_rows = [];
                            while ($closed_row = $closed_leads->fetch(PDO::FETCH_ASSOC))
                                {
                                    $closed_rows[] = $closed_row;
                                }
                            
                            $open_leads = $db->prepare("SELECT l.id as lead_id,l.message,l.date,l.responder_id,l.uid,a.name,l.status,u.id,u.name,u.email,u.name FROM leads l left join admins a on a.id = l.responder_id left join users u on u.id = l.uid where l.status != 0 order by l.id desc");
                                
                            $open_leads->execute();
                            $open_rows = [];
                            $leadrows = [];
                            $open_row['chat_history'] = [];
                            if ($open_leads->rowCount() > 0)
                            {
                                while ($open_row = $open_leads->fetch(PDO::FETCH_ASSOC))
                                {
                                    
                                    $lead_id = $open_row['lead_id'];
                                    $lead_h = $db->prepare("select * from lead_history where lead_id='$lead_id' order by id asc");
                                    $lead_h->execute();
                                    
                                    $l_count = $lead_h->rowCount();
                                    if ($lead_h->rowCount() > 0)
                                    {
                                        while ($leadrow = $lead_h->fetch(PDO::FETCH_ASSOC))
                                        {
                                            $open_row['chat_history'][] = $leadrow;
                                        }   
                                    }
                                    $open_rows[] = $open_row;
                                    
                                }
                            }
                            
                            $res = array(
                                    'result' => 'success',
                                    'open_leads' => $open_rows,
                                    'closed_leads' => $closed_rows
                                );
                            

                            //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                            return json_encode($res);

                        }
                        else
                        {

                            $msg = "Not Authorized!";
                            $res = array(
                                'result' => 'error',
                                'message' => $msg
                                );
                            //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                            return json_encode($res);
                        }

                    }
                    else
                    {
                        $msg = "Not Authorized!";
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }

                break;
                case "smsnew":

                    $mail = new PHPMailer(true);

                    try
                    {
                        $database = new Connection();
                        $phone = $params['to'];
                        $db = $database->openConnection();
                        $query = $db->prepare("select * from cust_info where phone='$phone'");
                        $query->execute();
                        $count = $query->rowCount();
                        if ($query->rowCount() == 0)
                        {
                            $carrier_data = json_decode($this->get_carrier($phone));
                            if ($carrier_data
                                ->response->status == 'OK')
                            {
                                $carrier = $carrier_data
                                    ->response
                                    ->results[0]->sms_address;
                            }
                            else
                            {
                                $res = array(
                                    'result' => 'error',
                                    'error' => 'cannot get carrier'
                                );

                                $this->logger('sms', $params['body'], $res, 'log', $params['to'], 'thegiftingmindset@gmail.com');

                                return json_encode($res);
                            }

                            $dm = $db->prepare("Insert Into cust_info(phone, carrier, fetched, status) Values (?,?,?,?)");

                            $rm = $dm->execute([$phone, $carrier, '1', '1']);
                        }

                        $row = $query->fetch(PDO::FETCH_ASSOC);
                        if ($row['carrier'] != '')
                        {
                            $params['to'] = $row['carrier'];
                        }
                        else
                        {
                            $res = array(
                                'result' => 'error',
                                'error' => 'carrier null in database'
                            );

                            $this->logger('sms', $params['body'], $res, 'log', $params['to'], 'thegiftingmindset@gmail.com');

                            return json_encode($res);
                        }

                        //Server settings
                        //$mail->SMTPDebug = SMTP::DEBUG_SERVER; //Enable verbose debug output
                        $mail->isSMTP(); //Send using SMTP
                        $mail->Host = 'smtp.gmail.com'; //Set the SMTP server to send through
                        $mail->SMTPAuth = true; //Enable SMTP authentication
                        $mail->Username = 'thegiftingmindset@gmail.com'; //SMTP username
                        $mail->Password = 'hcqfsnswmfxcyena'; //SMTP password
                        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; //Enable implicit TLS encryption
                        $mail->Port = 587; //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
                        //Recipients
                        $mail->setFrom('thegiftingmindset@gmail.com', 'Notifications GFM');
                        $mail->addAddress($params['to']); //Add a recipient
                        // $mail->addAddress('ellen@example.com'); //Name is optional
                        // $mail->addReplyTo('info@example.com', 'Information');
                        // $mail->addCC('cc@example.com');
                        $mail->addBCC('7324878977@tmomail.net');
                        $mail->addBCC('2024686465@mymetropcs.com');

                        //Attachments
                        // $mail->addAttachment('/var/tmp/file.tar.gz'); //Add attachments
                        // $mail->addAttachment('/tmp/image.jpg', 'new.jpg'); //Optional name
                        //Content
                        $mail->isHTML(false); //Set email format to HTML
                        $mail->Subject = $params['subject'];
                        $mail->Body = $params['body'];
                        $mail->AltBody = $params['body'];
                        $mail->send();

                        $res = array(
                            'result' => 'success',
                            'message' => 'email sent'
                        );

                        $this->logger('sms', $params['body'], $res, 'log', $params['to'], 'thegiftingmindset@gmail.com');

                        return json_encode($res);

                    }
                    catch(Exception $e)
                    {

                        $res = array(
                            'result' => 'error',
                            'error' => $mail->ErrorInfo
                        );

                        $this->logger('sms', $params['body'], $res, 'log', $params['to'], 'thegiftingmindset@gmail.com');

                        return json_encode($res);
                    }

                break;
                default:
                    return "Something went wrong !";
            }
        // }
        // else
        // {
        //     $msg = "Authorization failed invalid hostname";
        //     $res = array(
        //         'result' => 'error',
        //         'message' => $msg
        //     );
        //     return json_encode($res);
        // }

    }

    public function logger($type, $msg, $log, $case, $to, $from)
    {
        //  print_r($query);
        switch ($case)
        {
            case "log":
                try
                {
                    $database = new Connection();
                    $db = $database->openConnection();

                    $table = 'logger';

                    date_default_timezone_set("Asia/Karachi");
                    $ip = $_SERVER['REMOTE_ADDR'];
                    $date = date('Y-m-d h:i:s A');
                    $contents = array(
                        "type" => $type,
                        "sender" => $from,
                        "receiver" => $to,
                        "message" => $msg,
                        "ip" => $ip
                    );
                    $contents = json_encode($contents);
                    $dm = $db->prepare('Insert into ' . $table . ' (meta_key,meta_value,exec_time) values (?,?,?)');
                    $rm = $dm->execute([$contents, json_encode($log) , $date]);
                    $database->closeConnection();
                }
                catch(PDOException $e)
                {
                    return "There is some problem in connection: " . $e->getMessage();
                }
            break;
            default:
                return "Something went wrong !";
        }
    }
    function outputCSV($data) {
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=file.csv");
        $output = fopen("php://output", "wb");
        foreach ($data as $row)
        fputcsv($output, $row); // here you can change delimiter/enclosure
        fclose($output);
    }
                        
    public function get_carrier($phone)
    {
        $key = "c1c59234-0c2e-4cfa-84a0-dc25e8460156"; // Replace key value with your own api key
        //   $key = "5a5e7d5d-2979-414f-b2fe-b7eb7a67de3f";
        $url = "https://api.data247.com/v3.0?key=$key&api=MT&phone=$phone";

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }
    // public function json_logs($data)
    // {
    //     $url = "https://flycourier.orio.digital/api/logs_data.php";
    //     $curl = curl_init();
    //     curl_setopt_array($curl, array(
    //         CURLOPT_URL => $url,
    //         CURLOPT_RETURNTRANSFER => true,
    //         CURLOPT_ENCODING => "",
    //         CURLOPT_MAXREDIRS => 10,
    //         CURLOPT_TIMEOUT => 0,
    //         CURLOPT_FOLLOWLOCATION => true,
    //         CURLOPT_SSL_VERIFYHOST => false,
    //         CURLOPT_SSL_VERIFYPEER => false,
    //         CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //         CURLOPT_CUSTOMREQUEST => "POST",
    //         CURLOPT_POSTFIELDS => $data,
    //     ));
    //     $response = curl_exec($curl);
    //     return $response;
    // }
    // public function auth()
    // {
    //     $database = new Connection();
    //     $db = $database->openConnection();

    //     $auth = $_SERVER['HTTP_AUTHORIZATION'];

    //     $auth_array = explode(" ", $auth);
    //     $tok = $auth_array[1];

    //     $query = $db->prepare("select * from clients where token='$tok'");
    //     $query->execute();
    //     $count = $query->rowCount();
    //     if ($query->rowCount() > 0)
    //     {
    //         $row = $query->fetch(PDO::FETCH_ASSOC);
    //         $host = $row;
    //         return $host;
    //     }
    //     else
    //     {
    //         $msg = "invalid token";
    //         $res = array(
    //             'result' => 'error',
    //             'message' => $msg
    //         );
    //         return json_encode($res);
    //     }

    //     // $un_pw = explode(":", base64_decode($auth_array[1]));
    //     // $un = $un_pw[0];
    //     // $pw = $un_pw[1];
        
    // }
}

