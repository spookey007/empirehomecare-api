<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);
// require 'vendor/autoload.php';
include_once 'IRequest.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use phpseclib3\Net\SFTP;
use phpseclib3\Net\SSH2;
use yidas\csv\Writer;
use yidas\csv\Reader;
//define('NET_SSH2_LOG_REALTIME_FILENAME', 'vendor/log.txt');
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
            case "caregivers":
                return $this->verify_action('POST', $req, $case);
            break;
            case "visit":
                return $this->verify_action('POST', $req, $case);
            break;
            case "misc":
                return $this->verify_action('POST', $req, $case);
            break;
            case "members":
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
            case "caregivers":
                return $this->get_data('SELECT', $params, $case);
            break;
            case "visit":
                return $this->get_data('SELECT', $params, $case);
            break;
            case "misc":
                return $this->get_data('SELECT', $params, $case);
            break;
            case "members":
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
                $database = new Connection();
                $db = $database->openConnection();
                $table = 'user_info';
                $username = @$params['user_id'];
                $pwd = @$params['pwd'];
                $new_pwd = @$params['new_pwd'];
                $email = @$params['email'];
                $user_type = @$params['type_id'];
                $param_type = 'auth';
                if ($params['type'] == 'login')
                {
            
                    $query = $db->prepare("select * from $table  where JSON_EXTRACT(meta_value , '$.user_id') ='$username' and JSON_EXTRACT(meta_value , '$.pwd') = '$pwd' and JSON_EXTRACT(meta_value , '$.type_id') = '$user_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    
                    if ($query->rowCount() > 0)
                    {
                        $row = $query->fetch(PDO::FETCH_ASSOC);
                        $tt = json_decode($row['meta_value']);
                        @$dd = array(
                            'user_id' => $tt->user_id,
                            'email' => $tt->email,
                            'first_name' => $tt->first_name,
                            'last_name' => $tt->last_name,
                            'phone' => $tt->phone,
                            'misc' => @$tt->misc
                        );
                        if ($row['status'] == 1)
                        {
                            $msg = "Authenticated";
                            $res = array(
                                'result' => 'success',
                                'message' => $msg,
                                'data' => $dd
                            );
                            //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                            
                        }
                        else
                        {
                            $msg = "User inactive";
                            $res = array(
                                'result' => 'error',
                                'message' => $msg
                            );
                            //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                            
                        }
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

                }
                if ($params['type'] == 'forgetuser')
                {
                    $query = $db->prepare("select * from $table where JSON_EXTRACT(meta_value , '$.email') ='$email' and JSON_EXTRACT(meta_value , '$.type_id') = '$user_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {
                        $row = $query->fetch(PDO::FETCH_ASSOC);
                        $pwd = $row['pwd'];
                        $user = $row['user_name'];
                        $subject = 'Forget Password Request';
                        $msg = 'we have received a request for forget password, below is your requested information:<br> username:' . $user . '<br>password:' . $pwd;

                        $this->emailer($msg, $subject, $email);
                        $msg = "Password has been sent to your email address ";
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);

                    }
                    else
                    {
                        $msg = "no account found against provided email";
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }
                }
                if ($params['type'] == 'createuser')
                {

                    $query = $db->prepare("select * from $table where JSON_EXTRACT(meta_value , '$.email') ='$email'");
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {
                        $msg = "Account already exists against provided email";
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);

                    }
                    else
                    {
                        $query = $db->prepare("select * from $table where JSON_EXTRACT(meta_value , '$.user_id') ='$username'");
                        $query->execute();
                        $count = $query->rowCount();
                        if ($query->rowCount() > 0)
                        {

                            $msg = "username already exists";
                            $res = array(
                                'result' => 'error',
                                'message' => $msg
                            );
                            //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                            return json_encode($res);
                        }
                        else
                        {
                            $contents = json_encode($params);
                            $dm = $db->prepare('Insert into ' . $table . ' (meta_key,meta_value) values (?,?)');
                            $rm = $dm->execute([$param_type, $contents]);
                            $id = $db->lastInsertId();
                            
                            $query = $db->prepare("select * from $table  where id = '$id '");
                            $query->execute();
                            $count = $query->rowCount();
                            if ($query->rowCount() > 0)
                            {
                            $row = $query->fetch(PDO::FETCH_ASSOC);
                            $tt = json_decode($row['meta_value']);
                            @$dd = array(
                                'user_id' => $tt->user_id,
                                'email' => $tt->email,
                                'first_name' => $tt->first_name,
                                'last_name' => $tt->last_name,
                                'phone' => $tt->phone,
                                'misc' => @$tt->misc
                            );
                            
                            }
                            $msg = "Account created successfully";
                            $res = array(
                                'result' => 'success',
                                'message' => $msg,
                                'data' => $dd
                            );
                            //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                            return json_encode($res);
                        }

                    }

                }
                if ($params['type'] == 'deactivateuser')
                {
                    $query = $db->prepare("select * from $table where JSON_EXTRACT(meta_value , '$.email') ='$email' and JSON_EXTRACT(meta_value , '$.user_id') = '$user_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {
                        $stmt = $db->prepare("UPDATE  $table  SET status = ? where JSON_EXTRACT(meta_value , '$.email') = ? and JSON_EXTRACT(meta_value , '$.user_id') = ? ");
                        $stmt->execute([0, $email, $user_type]);

                        $msg = "account has been deactivated";
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);

                    }
                    else
                    {
                        $msg = "no account found against provided email";
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }
                }
                
                if ($params['type'] == 'resetpwd')
                {
                    $query = $db->prepare("select * from $table where JSON_EXTRACT(meta_value , '$.email') ='$email' and JSON_EXTRACT(meta_value , '$.pwd') = '$pwd' and JSON_EXTRACT(meta_value , '$.type_id') = '$user_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {
                        $stmt = $db->prepare("UPDATE  $table  SET JSON_EXTRACT(meta_value , '$.pwd') = ? where JSON_EXTRACT(meta_value , '$.email') = ? and JSON_EXTRACT(meta_value , '$.type_id') = ? ");
                        $stmt->execute([$new_pwd,$email, $user_type]);

                        $msg = "password has been updated";
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);

                    }
                    else
                    {
                        $msg = "wrong current password provided";
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }
                }
                $database->closeConnection();
            break;

            case "misc":
                $database = new Connection();
                $db = $database->openConnection();
                // $table = 'member_teams';
                @$id = $params['id'];
                @$name = $params['name'];
                @$param_type = $params['type'];
                @$active = $params['active'];
                @$tag = $params['tag'];
                @$filter = $params['filter'];
                //team members
                $table = 'misc';
                if ($params['type'] == 'gettmembers')
                {
                    $query = $db->prepare('select c.id,c.status,c.datetime,JSON_SET(c.meta_value, "$.id", c.id,"$.active", c.status) as meta_value  from ' . $table . ' as c where c.status=' . $active . ' and c.meta_key = ' . "'$param_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    $rows = [];
                    $msg = 'total rows ' . $count;
                    if ($query->rowCount() > 0)
                    {
                        while ($mems = $query->fetch(PDO::FETCH_ASSOC))
                        {
                            $rows[] = json_decode($mems['meta_value']);
                        }

                    }
                    $res = array(
                        'result' => 'success',
                        'message' => $msg,
                        'data' => $rows
                    );

                    return json_encode($res);
                }
                if ($params['type'] == 'addtmembers')
                {
                    unset($params['type']);
                    $query = $db->prepare("select * from  $table where JSON_EXTRACT(meta_value , '$.name') = '$name' and meta_key = '$param_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {
                        $msg = "entity already exists";
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);

                    }
                    else
                    {

                        $contents = json_encode($params);
                        $dm = $db->prepare('Insert into ' . $table . ' (meta_key,meta_value) values (?,?)');
                        $rm = $dm->execute([$param_type, $contents]);

                        $msg = "Entity created successfully";
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);

                    }

                }

                if ($params['type'] == 'updtmembers')
                {

                    $query = $db->prepare("select * from $table where id ='$id' and meta_key = '$param_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {

                        unset($params['type']);
                        unset($params['active']);
                        $contents = json_encode($params);
                        $stmt = $db->prepare("UPDATE  $table  SET status = ?,meta_value =? where id = ? and meta_key = ?");
                        $stmt->execute([$active, $contents, $id, $param_type]);
                        $database->closeConnection();
                        $msg = 'entity updated';
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        return json_encode($res);

                    }
                    else
                    {
                        $msg = "no entity found against id # " . $id;
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }
                }

                if ($params['type'] == 'deltmembers')
                {
                    $query = $db->prepare("select * from $table where id ='$id' and meta_key = '$param_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {
                        $stmt = $db->prepare("UPDATE $table SET status = ? where id = ? and meta_key = '$param_type'");
                        $stmt->execute([0, $id]);

                        $msg = "Entity deactivated";
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);

                    }
                    else
                    {
                        $msg = "Entity not found";
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }
                }

                //coordinator
                $table = 'misc';
                $param_type = 'addcordinator';
                if ($params['type'] == 'getcordinator')
                {
                    $query = $db->prepare('select c.id,c.status,c.datetime,JSON_SET(c.meta_value, "$.id", c.id,"$.active", c.status) as meta_value  from ' . $table . ' as c where c.status=' . $active . ' and c.meta_key = ' . "'$param_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    $rows = [];
                    $msg = 'total rows ' . $count;
                    if ($query->rowCount() > 0)
                    {
                        while ($mems = $query->fetch(PDO::FETCH_ASSOC))
                        {
                            $rows[] = json_decode($mems['meta_value']);
                        }

                    }
                    $res = array(
                        'result' => 'success',
                        'message' => $msg,
                        'data' => $rows
                    );

                    return json_encode($res);
                }
                if ($params['type'] == 'addcordinator')
                {
                    unset($params['type']);
                    $query = $db->prepare("select * from  $table where JSON_EXTRACT(meta_value , '$.name') = '$name' and meta_key = '$param_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {
                        $msg = "entity already exists";
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);

                    }
                    else
                    {

                        $contents = json_encode($params);
                        $dm = $db->prepare('Insert into ' . $table . ' (meta_key,meta_value) values (?,?)');
                        $rm = $dm->execute([$param_type, $contents]);

                        $msg = "Entity created successfully";
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);

                    }

                }

                if ($params['type'] == 'updcordinator')
                {

                    $query = $db->prepare("select * from $table where id ='$id' and meta_key = '$param_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {

                        unset($params['type']);
                        unset($params['active']);
                        $contents = json_encode($params);
                        $stmt = $db->prepare("UPDATE  $table  SET status = ?,meta_value =? where id = ? and meta_key = ?");
                        $stmt->execute([$active, $contents, $id, $param_type]);
                        $database->closeConnection();
                        $msg = 'entity updated';
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        return json_encode($res);

                    }
                    else
                    {
                        $msg = "no entity found against id # " . $id;
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }
                }

                if ($params['type'] == 'delcordinator')
                {
                    $query = $db->prepare("select * from $table where id ='$id' and meta_key = '$param_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {
                        $stmt = $db->prepare("UPDATE $table SET status = ? where id = ? and meta_key = '$param_type'");
                        $stmt->execute([0, $id]);

                        $msg = "Entity deactivated";
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);

                    }
                    else
                    {
                        $msg = "Entity not found";
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }
                }
                //MCO
                $table = 'misc';
                $param_type = 'addmco';
                if ($params['type'] == 'getmco')
                {

                    //$query = $db->prepare("select c.id,c.status,c.datetime,JSON_SET(c.meta_value, "$.id", c.id,"$.member_status", c.status) as meta_value from $table c where c.meta_key = '$param_type' and c.status!='$active' ");
                    $query = $db->prepare('select c.id,c.status,c.datetime,JSON_SET(c.meta_value, "$.id", c.id,"$.active", c.status) as meta_value  from ' . $table . ' as c where c.status=' . $active . ' and c.meta_key = ' . "'$param_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    $rows = [];
                    $msg = 'total rows ' . $count;
                    if ($query->rowCount() > 0)
                    {
                        while ($mems = $query->fetch(PDO::FETCH_ASSOC))
                        {
                            $rows[] = json_decode($mems['meta_value']);
                        }

                    }
                    $res = array(
                        'result' => 'success',
                        'message' => $msg,
                        'data' => $rows
                    );

                    return json_encode($res);
                }
                if ($params['type'] == 'addmco')
                {
                    unset($params['type']);
                    $query = $db->prepare("select * from  $table where JSON_EXTRACT(meta_value , '$.name') = '$name' and meta_key = '$param_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {
                        $msg = "entity already exists";
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);

                    }
                    else
                    {

                        $contents = json_encode($params);
                        $dm = $db->prepare('Insert into ' . $table . ' (meta_key,meta_value) values (?,?)');
                        $rm = $dm->execute([$param_type, $contents]);

                        $msg = "Entity created successfully";
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);

                    }

                }
                if ($params['type'] == 'delmco')
                {
                    $query = $db->prepare("select * from $table where id ='$id' and meta_key = '$param_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {
                        $stmt = $db->prepare("UPDATE $table SET status = ? where id = ? and meta_key = '$param_type'");
                        $stmt->execute([0, $id]);

                        $msg = "Entity deactivated";
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);

                    }
                    else
                    {
                        $msg = "Entity not found";
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }
                }
                if ($params['type'] == 'updmco')
                {
                    $query = $db->prepare("select * from $table where id ='$id' and meta_key = '$param_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {

                        unset($params['type']);
                        unset($params['active']);
                        $contents = json_encode($params);
                        $stmt = $db->prepare("UPDATE  $table  SET status = ?,meta_value =? where id = ? and meta_key = ?");
                        $stmt->execute([$active, $contents, $id, $param_type]);
                        $database->closeConnection();
                        $msg = 'entity updated';
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        return json_encode($res);

                    }
                    else
                    {
                        $msg = "no entity found against id # " . $id;
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }
                }
                $param_type = 'addmisc';
                if ($params['type'] == 'addmisc')
                {
                    unset($params['type']);
                    $query = $db->prepare("select * from  $table where JSON_EXTRACT(meta_value , '$.$filter') = '$tag' and meta_key = '$param_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {
                        $msg = "entity already exists";
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);

                    }
                    else
                    {

                        $contents = json_encode($params);
                        $dm = $db->prepare('Insert into ' . $table . ' (meta_key,meta_value) values (?,?)');
                        $rm = $dm->execute([$param_type, $contents]);

                        $msg = "Entity created successfully";
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);

                    }

                }
                if ($params['type'] == 'getmisc')
                {
                    // print_r('select c.id,c.status,c.datetime,JSON_SET(c.meta_value, "$.id", c.id,"$.active", c.status) as meta_value  from ' . $table . ' as c where c.status=' . $active . ' and JSON_EXTRACT(meta_value , "$.page") = "'.$page.'" and c.meta_key = ' . "'$param_type'");
                    // exit();
                    $where = '';
                //     @$tag = $params['tag'];
                // @$filter = $params['filter'];
                    $whr = "$.$filter";
                    if(isset($tag)){
                        $where = 'and JSON_EXTRACT(meta_value , "'.$whr.'") = "'.$tag.'"';
                    }
                    // print_r('select c.id,c.status,c.datetime,JSON_SET(c.meta_value, "$.id", c.id,"$.active", c.status) as meta_value  from ' . $table . ' as c where c.status=' . $active . ' '.$where.' and c.meta_key = ' . "'$param_type'");
                    // exit();
                    //$query = $db->prepare("select c.id,c.status,c.datetime,JSON_SET(c.meta_value, "$.id", c.id,"$.member_status", c.status) as meta_value from $table c where c.meta_key = '$param_type' and c.status!='$active' ");
                    $query = $db->prepare('select c.id,c.status,c.datetime,JSON_SET(c.meta_value, "$.id", c.id,"$.active", c.status) as meta_value  from ' . $table . ' as c where c.status=' . $active . ' '.$where.' and c.meta_key = ' . "'$param_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    $rows = [];
                    $msg = 'total rows ' . $count;
                    if ($query->rowCount() > 0)
                    {
                        while ($mems = $query->fetch(PDO::FETCH_ASSOC))
                        {
                            $rows[] = json_decode($mems['meta_value']);
                        }

                    }
                    $res = array(
                        'result' => 'success',
                        'message' => $msg,
                        'data' => $rows
                    );

                    return json_encode($res);
                }
                
                $database->closeConnection();
            break;

            case "members":
                //members
                $database = new Connection();
                $db = $database->openConnection();
                $table = 'members';
                @$id = $params['id'];
                @$member_name = $params['member_name'];
                @$PHIMemberID = $params['PHIMemberID'];
                @$MemberID = $params['MemberID'];
                $param_type = 'addmembers';
                //team members
                if ($params['type'] == 'getmembers')
                {
                    $query = $db->prepare("select * from $table ");
                    $query->execute();
                    $count = $query->rowCount();
                    $rows = [];
                    $msg = 'total rows ' . $count;
                    if ($query->rowCount() > 0)
                    {
                        while ($mems = $query->fetch(PDO::FETCH_ASSOC))
                        {
                            $rows[] = json_decode($mems['meta_value']);
                        }

                    }
                    $res = array(
                        'result' => 'success',
                        'message' => $msg,
                        'data' => $rows
                    );

                    return json_encode($res);
                }
                if ($params['type'] == 'addmembers')
                {
                    unset($params['type']);
                    $query = $db->prepare("select * from  $table  where JSON_EXTRACT(meta_value , '$.PHIMemberID') = '$PHIMemberID'");
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {
                        $msg = "entity already exists";
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);

                    }
                    else
                    {
                        $contents = json_encode($params);
                        $dm = $db->prepare('Insert into ' . $table . ' (meta_key,meta_value) values (?,?)');
                        $rm = $dm->execute(['addmembers', $contents]);

                        $msg = "Entity created successfully";
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);

                    }

                }
                
                if ($params['type'] == 'updatemembers')
                {

                    $query = $db->prepare("select * from $table where JSON_EXTRACT(meta_value , '$.MemberID') = '$MemberID'");
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {

                        unset($params['type']);
                        unset($params['active']);
                        $contents = json_encode($params);
                        $stmt = $db->prepare("UPDATE  $table  SET meta_value =? where JSON_EXTRACT(meta_value , '$.MemberID') = ? and meta_key = ?");
                        $stmt->execute([$contents, $MemberID, $param_type]);
                        $database->closeConnection();
                        $msg = 'entity updated';
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        return json_encode($res);

                    }
                    else
                    {
                        $msg = "no entity found against id # " . $id;
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }
                }
                if ($params['type'] == 'assignpoc')
                {
                    unset($params['type']);
                    
                    $member_id = $params['member_id'];
                    $table = 'member_misc';
                    $param_type = 'addpoc';
                    $query = $db->prepare("select * from  $table  where JSON_EXTRACT(meta_value , '$.member_id') = '$member_id' and meta_key = '$param_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {
                        if($params['is_primary']=='Y'){
                            while ($pocs = $query->fetch(PDO::FETCH_ASSOC))
                            {
                                $idd = $pocs['id'];
                                $stmt = $db->prepare("UPDATE $table SET meta_value = JSON_SET(meta_value, '$.is_primary',:value) WHERE id = :id");
                                $stmt->execute([
                                    ':value' => 'N',
                                    ':id' => $idd
                                ]);
                                 
                                
                            }
                        
                        }
                        $contents = json_encode($params);
                        $dm = $db->prepare('Insert into ' . $table . ' (meta_key,meta_value) values (?,?)');
                        $rm = $dm->execute(['addpoc', $contents]);
                        
                        $msg = "Entity created successfully";
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                            );
                        return json_encode($res);
                    }
                    else
                    {
                        $contents = json_encode($params);
                        $dm = $db->prepare('Insert into ' . $table . ' (meta_key,meta_value) values (?,?)');
                        $rm = $dm->execute(['addpoc', $contents]);

                        $msg = "Entity created successfully";
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);

                    }

                }
                if ($params['type'] == 'getpoc')
                {
                    $member_id = $params['member_id'];
                    $table = 'member_misc';
                    $param_type = 'addpoc';
                    $query = $db->prepare("select JSON_SET(meta_value, '$.id', id) meta_value  from  $table  where JSON_EXTRACT(meta_value , '$.member_id') = '$member_id' and meta_key = '$param_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    $rows = [];
                    $msg = 'total rows ' . $count;
                    if ($query->rowCount() > 0)
                    {
                        while ($mems = $query->fetch(PDO::FETCH_ASSOC))
                        {
                            $rows[] = json_decode($mems['meta_value']);
                        }

                    }
                    $res = array(
                        'result' => 'success',
                        'message' => $msg,
                        'data' => $rows
                    );

                    return json_encode($res);
                }
                
                if ($params['type'] == 'addmasterweek')
                {
                    unset($params['type']);
                    $table = 'member_misc';
                    $param_type = 'addmasterweek';
                    // $query = $db->prepare("select * from  $table  where JSON_EXTRACT(meta_value , '$.member_id') = '$member_id' and meta_key = '$param_type'");
                    // $query->execute();
                    // $count = $query->rowCount();
                    // if ($query->rowCount() > 0)
                    // {
                    //     $msg = "entity already exists";
                    //     $res = array(
                    //         'result' => 'error',
                    //         'message' => $msg
                    //     );
                    //     //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                    //     return json_encode($res);

                    // }
                    // else
                    // {
                        $contents = json_encode($params);
                        $dm = $db->prepare('Insert into ' . $table . ' (meta_key,meta_value) values (?,?)');
                        $rm = $dm->execute(['addmasterweek', $contents]);

                        $msg = "Entity created successfully";
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);

                    // }

                }
                
                if ($params['type'] == 'getmasterweek')
                {
                    $member_id = $params['member_id'];
                    $table = 'member_misc';
                    $param_type = 'addmasterweek';
                    $query = $db->prepare("select JSON_SET(meta_value, '$.id', id) meta_value  from  $table  where JSON_EXTRACT(meta_value , '$.member_id') = '$member_id' and meta_key = '$param_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    $rows = [];
                    $msg = 'total rows ' . $count;
                    if ($query->rowCount() > 0)
                    {
                        while ($mems = $query->fetch(PDO::FETCH_ASSOC))
                        {
                            $rows[] = json_decode($mems['meta_value']);
                        }

                    }
                    $res = array(
                        'result' => 'success',
                        'message' => $msg,
                        'data' => $rows
                    );

                    return json_encode($res);
                }
                if ($params['type'] == 'updatemasterweek')
                {

                    $member_id = $params['member_id'];
                    $m_id = $params['id'];
                    $table = 'member_misc';
                    $param_type = 'addmasterweek';
                    $query = $db->prepare("select JSON_SET(meta_value, '$.id', id) meta_value from $table where JSON_EXTRACT(meta_value , '$.member_id') = '$member_id' and id = '$m_id' and meta_key = '$param_type'");
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {

                        unset($params['type']);
                        unset($params['member_status']);
                        $contents = json_encode($params);
                        $stmt = $db->prepare("UPDATE  $table  SET meta_value =? where JSON_EXTRACT(meta_value , '$.MemberID') = ? and id = ?");
                        $stmt->execute([$contents,$member_id,$m_id]);
                        $database->closeConnection();
                        $msg = "Entity updated";
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        return json_encode($res);

                    }
                    else
                    {
                        $msg = "Entity not found";
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }
                }
                if ($params['type'] == 'delmco')
                {
                    $query = $db->prepare("select * from $table where id ='$id'");
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {
                        $stmt = $db->prepare("UPDATE $table SET status = ? where id = ?");
                        $stmt->execute([0, $id]);

                        $msg = "Entity deactivated";
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);

                    }
                    else
                    {
                        $msg = "Entity not found";
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }
                }
                if ($params['type'] == 'updmco')
                {
                    $query = $db->prepare("select * from $table where id ='$id'");
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {
                        $stmt = $db->prepare("UPDATE $table SET name = ? where id = ?");
                        $stmt->execute([$name, $id]);

                        $msg = "Entity updated";
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);

                    }
                    else
                    {
                        $msg = "Entity not found";
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }
                }
                $database->closeConnection();
            break;
            case "visit":
                $param_type = 'postvisit';
                $database = new Connection();
                $db = $database->openConnection();
                $table = 'visits';
                if ($params['type'] == 'authhistory')
                {
                    $table = 'auth_history';
                    
                    $where = '';
                    $where = '';
                    $updwhere = '';
                    if(isset($params['member_id'])){
                        $member_id = $params['member_id'];
                        $where .= 'and member_id = '.$member_id;
                    }
                    // print_r('select id, meta_value from ' . $table . ' v  where status=1 '.$where.' order by insert_date desc limit 3');
                    // exit();
                    $query = $db->prepare('select id, meta_value from ' . $table . ' v  where status=1 '.$where.' order by insert_date desc limit 3');
                    $query->execute();
                    $count = $query->rowCount();
                    $rows = [];
                    $msg = 'fetched last ' . $count . ' visits';
                    if ($query->rowCount() > 0)
                    {
                        while ($visits = $query->fetch(PDO::FETCH_ASSOC))
                        {
                            
                            $rows[] = json_decode($visits['meta_value']);
                        }

                    }
                //     $rows = array_map(function ($obj) {
                //     $new_obj = new stdClass();
                //     foreach ($obj as $key => $value) {
                //         $new_key = str_replace(' ', '', $key);
                //         $new_obj->$new_key = $value;
                //     }
                //     return $new_obj;
                // }, $rows);

                    $res = array(
                        'result' => 'success',
                        'message' => $msg,
                        'data' => $rows
                    );

                    return json_encode($res);
                    
                
                    
                }
                if ($params['type'] == 'postvisit')
                {
                    // ini_set('display_errors', 1);
                    // ini_set('display_startup_errors', 1);
                    // error_reporting(E_ALL);
                    unset($params['type']);
                    $contents = json_encode($params);
                    $arr = '';
                    $arr = json_decode($contents, TRUE);
                    // $ins_date = date('Y-m-d');
                    if(isset($params['MemberID'])){
                        $MemberID = $params['MemberID'];
                        
                        $query = $db->prepare("select * from members where JSON_EXTRACT(meta_value , '$.MemberID') = '$MemberID'");
                        $query->execute();
                        $count = $query->rowCount();
                        if ($query->rowCount() > 0)
                        {
                            $row = $query->fetch(PDO::FETCH_ASSOC);
                            $dd = json_decode($row['meta_value']);
                                
                            $from_date = new DateTime($params['ScheduleStartTime']);
                            $fr_date = $from_date->format('m-Y');
                            $ins_date = $from_date->format('Y-m-d');
                            
                            $to_date = new DateTime($params['ScheduleEndTime']);
                            // print_r("select * from auth_history where member_id = '$MemberID' and DATE_FORMAT(datetime, '%m-%Y') = '$fr_date'");
                            // exit();
                            $query = $db->prepare("select * from auth_history where member_id = '$MemberID' and DATE_FORMAT(insert_date, '%m-%Y') = '$fr_date' ");
                            $query->execute();
                            $count = $query->rowCount();
                            
                            if ($query->rowCount() > 0)
                            {
                                $rowsub = $query->fetch(PDO::FETCH_ASSOC);
                                $dds = json_decode($rowsub['meta_value']);
                      
                                $arr['auth'] = $dds;
                                $contents = json_encode($arr);
                            }
                           
                            else{
                                
                                $arr['auth'] = array(
                                    'id' => $this->generateRandomString(5),
                                    'service_code' => $params['ProcedureCode'],
                                    'service_type' => $dd->AcceptedServices,
                                    'service_code_type' => 'Hourly (Mutual + Member Shift Overlap)',
                                    'service_category' => 'Home Health',
                                    'from_date' => $from_date->format('m/d/Y'),
                                    'to_date' => $to_date->format('m/d/Y'),
                                    'auth_type' => 'entire period',
                                    'hours_per_auth' => '',
                                    'add_rules' => '',
                                    'billing_diag_code' => array('code'=>'R69','description'=>'Illness, unspecified','admit'=>'Y','primary'=>'Y'),
                                    'notes' => 'test',
                                    
                                );
                                $contents = json_encode($arr);
                                $arrd = json_encode($arr['auth']);
                                $d11 = $db->prepare('Insert into auth_history (meta_key,meta_value,member_id,insert_date) values (?,?,?,?)');
                                $r11 = $d11->execute([$param_type, $arrd,$MemberID,$ins_date]);
                    
                    
                            }
                            // $contents = json_decode($contents);
                            // unset($contents->auth);
                            // $contents = json_encode($contents);
                        }
                    
                    
                    }
                    $dm = $db->prepare('Insert into ' . $table . ' (meta_key,meta_value) values (?,?)');
                    $rm = $dm->execute([$param_type, $contents]);
                    $id = $db->lastInsertId();
                    
                    $d1 = $db->prepare('Insert into visits_history (id,meta_key,meta_value) values (?,?,?)');
                    $r1 = $d1->execute([$id,$param_type, $contents]);
                    
                    
                    $database->closeConnection();
                    
                    $rows = [["Agency Tax ID", "Office NPI", "Payer ID", "Medicaid Number", "Member First Name", "Member Last Name", "Member ID", "Caregiver Code", "Caregiver Registry ID", "Caregiver License Number", "Caregiver First Name", "Caregiver Last Name", "Caregiver Gender", "Caregiver Date of Birth", "Caregiver SSN", "Caregiver Email", "Schedule ID", "Visit ID", "Procedure Code", "Diagnosis Code", "Schedule Start Time", "Schedule End Time", "Visit Start Time", "Visit End Time", "EVV Start Time", "EVV End Time", "Clock-In Service Location Address Line 1", "Clock-In Service Location Address Line 2", "Clock-In Service Location City", "Clock-In Service Location State ", "Clock-In Service Location Zip Code", "Clock-In Service Location Type", "Clock-Out Service Location Address Line 1", "Clock-Out Service Location Address Line 2", "Clock-Out Service Location City", "Clock-Out Service Location State ", "Clock-Out Service Location Zip Code", "Clock-Out Service Location Type", "Duties", "Clock-In Phone Number", "Clock-In Latitude", "Clock-In Longitude", "Clock-In EVV Other Info", "Clock-Out Phone Number", "Clock-Out Latitude", "Clock-Out Longitude", "Clock-Out EVV Other Info", "Invoice Number", "Visit Edit Reason Code", "Visit Edit Action Taken", "Visit Edit Made By", "Notes", "Is Deletion", "Invoice Line-Item ID", "Total Billed Amount", "Units Billed", "Billed Rate", "Submission Type", "TRN Number", "Enable Secondary Billing", "Other Subscriber ID", "Primary Payer ID", "Primary Payer Name", "Relationship to Insured", "Primary Payer Policy or Group number", "Primary Payer Program Name", "Plan Type", "Total Paid Amount", "Total Paid Units", "Paid Date", "Deductible", "Coinsurance", "Copay", "Contracted Adjustments", "Not Medically Necessary", "Non-Covered Charges", "Max Benefit Exhausted", "Missed Visit", "Missed Visit Reason Code", "Missed Visit Action Taken Code", "Missed Visit Notes", "Travel Time Request Hours", "Travel Time Comments", "Cancel Travel Time Request", "Timesheet Required", "Timesheet Approved", "User Field 1", "User Field 2", "User Field 3", "User Field 4", "User Field 5", "User Field 6", "User Field 7", "User Field 8", "User Field 9", "User Field 10"], $params];
                   
                    //VISITS_AgencyTaxID_YYYYMMDDHHMMSS.CSV
                    $path = $_SERVER['DOCUMENT_ROOT'] . '/empire/test.csv';
                    
                    $filename = 'VISITS_' . $params['AgencyTaxID'] . '_' . date('Ymdhms') . '.CSV';
                   
                    $fp = fopen($path, 'w'); // open in write only mode (write at the start of the file)
                   
                    $delimiter = ',';
                    $enclosure = '"';
                    
                   
                    $eol = "\r\n";

                    $dd = $this->fputcsv_eol($fp, $rows, $eol);
                   
                    // $sftp = new SFTP('kristenlaw.penntelco.com', 2112);

                    // if (!$sftp->login('root', 'Galico@1214'))
                    // {
                    //     throw new \Exception('Host key verification failed');
                    // }
                    // else
                    // {
                    //     $sftp->put('/var/www/html/' . $filename, $path, SFTP::SOURCE_LOCAL_FILE);

                    // }

                    $msg = 'Visit posted';
                    $res = array(
                        'result' => 'success',
                        'message' => $msg,
                        'visit_id'=> $id
                    );
                    return json_encode($res);
                }
                if ($params['type'] == 'getvisit')
                {
                    
                    $where = '';
                    $where = '';
                    $updwhere = '';
                    if(isset($params['id'])){
                        $id = $params['id'];
                        $where = 'and v.id = '.$id;
                        $updwhere .=' and v.id = ?';
                        $value = $id;
                    }
                    if(isset($params['member_id'])){
                        $member_id = $params['member_id'];
                        $where .= 'and JSON_EXTRACT(v.meta_value , "$.MemberID") = '.$member_id;
                        $updwhere .=' and JSON_EXTRACT(v.meta_value , "$.MemberID") = ?';
                        $value = $member_id;
                    }
                    if(isset($params['caregiver_code'])){
                        $ccode = $params['caregiver_code'];
                        $where .= ' and JSON_EXTRACT(v.meta_value , "$.CaregiverCode") = '."'$ccode'";
                        $updwhere .=' and JSON_EXTRACT(v.meta_value , "$.CaregiverCode") = ?';
                        $value = $ccode;
                    }
                    
                    // print_r("SELECT v.id, JSON_SET(v.meta_value, '$.id', v.id, '$.visit_status', v.status, '$.member_data', REPLACE(JSON_UNQUOTE(mm.member_data), '\\\', '') ) AS meta_value FROM visits v INNER JOIN members m ON JSON_EXTRACT(m.meta_value, '$.MemberID') = JSON_EXTRACT(v.meta_value, '$.MemberID') LEFT JOIN ( SELECT JSON_SET(JSON_UNQUOTE(meta_value),'$.id', id) AS member_data, JSON_EXTRACT(meta_value, '$.member_id') AS member_id FROM member_misc WHERE JSON_EXTRACT(meta_value, '$.is_primary') = 'Y' ) mm ON mm.member_id = JSON_EXTRACT(m.meta_value, '$.MemberID') WHERE v.status = 1".$where);
                    // exit();
                    // print_r("SELECT v.id, JSON_SET(v.meta_value,
                    //                             '$.id', v.id,
                    //                             '$.visit_status', v.status,
                    //                             '$.member_data', REPLACE(JSON_UNQUOTE(mm.meta_value), '\\\', '')
                    //                         ) AS meta_value
                    //                         FROM visits v 
                    //                         INNER JOIN members m ON JSON_EXTRACT(m.meta_value, '$.MemberID') = JSON_EXTRACT(v.meta_value, '$.MemberID')
                    //                         LEFT JOIN member_misc mm ON JSON_EXTRACT(mm.meta_value, '$.member_id') = JSON_EXTRACT(m.meta_value, '$.MemberID') AND JSON_EXTRACT(mm.meta_value, '$.is_primary') = 'Y'
                    //                         LEFT JOIN misc mi ON JSON_EXTRACT(mi.meta_value, '$.task_id') = JSON_EXTRACT(m.meta_value, '$.task_id') AND mi.id = 36
                    //                         WHERE v.status = 1 ".$where);
                    // exit();
                     $spec_concat = '"';
                    
                    $dummy_json = '{"id": "", "POC": [{"duty": "", "task_id": "", "category": "Nutrition", "": "", "Instruction": "", "days_of_week": "", "times_a_week_max": "", "times_a_week_min": ""}], "shift": "", "end_date": "", "member_id": "200855215", "is_primary": "Y", "start_date": ""}';
                    
                    // print_r("SELECT v.id, JSON_SET( v.meta_value, '$.id', v.id, '$.visit_status', v.status, '$.member_data', (CASE WHEN mm.member_data!='' THEN JSON_UNQUOTE(mm.member_data) ELSE '$dummy_json' END) ) AS meta_value FROM visits v INNER JOIN members m ON JSON_EXTRACT(m.meta_value, '$.MemberID') = JSON_EXTRACT(v.meta_value, '$.MemberID') LEFT JOIN ( SELECT ( CASE WHEN meta_value != '' THEN JSON_SET( JSON_UNQUOTE(meta_value), '$.id', id ) ELSE '' END ) AS member_data, ( CASE WHEN meta_value != '' THEN JSON_EXTRACT(meta_value, '$.member_id') ELSE '' END ) AS member_id FROM member_misc WHERE JSON_EXTRACT(meta_value, '$.is_primary') = 'Y' ) mm ON trim( both '$spec_concat' from mm.member_id ) = trim( both '$spec_concat' from JSON_EXTRACT(m.meta_value, '$.MemberID') ) WHERE v.status = 1".$where);
                    // exit();
                    $query = $db->prepare("SELECT v.id, JSON_SET( v.meta_value, '$.id', v.id, '$.visit_status', v.status, '$.member_data', (CASE WHEN mm.member_data!='' THEN JSON_UNQUOTE(mm.member_data) ELSE '$dummy_json' END) ) AS meta_value FROM visits v INNER JOIN members m ON JSON_EXTRACT(m.meta_value, '$.MemberID') = JSON_EXTRACT(v.meta_value, '$.MemberID') LEFT JOIN ( SELECT ( CASE WHEN meta_value != '' THEN JSON_SET( JSON_UNQUOTE(meta_value), '$.id', id ) ELSE '' END ) AS member_data, ( CASE WHEN meta_value != '' THEN JSON_EXTRACT(meta_value, '$.member_id') ELSE '' END ) AS member_id FROM member_misc WHERE JSON_EXTRACT(meta_value, '$.is_primary') = 'Y' ) mm ON trim( both '$spec_concat' from mm.member_id ) = trim( both '$spec_concat' from JSON_EXTRACT(m.meta_value, '$.MemberID') ) WHERE v.status = 1".$where);
                    $query->execute();
                    $count = $query->rowCount();
                    $rows = [];
                    $msg = 'fetched total ' . $count . ' visits';
                    if ($query->rowCount() > 0)
                    {
                        while ($visits = $query->fetch(PDO::FETCH_ASSOC))
                        {
                            
                            $rows[] = json_decode($visits['meta_value']);

                        }

                    }
                //     $rows = array_map(function ($obj) {
                //     $new_obj = new stdClass();
                //     foreach ($obj as $key => $value) {
                //         $new_key = str_replace(' ', '', $key);
                //         $new_obj->$new_key = $value;
                //     }
                //     return $new_obj;
                // }, $rows);

                    $res = array(
                        'result' => 'success',
                        'message' => $msg,
                        'data' => $rows
                    );

                    return json_encode($res);
                }
                
                if ($params['type'] == 'editvisit')
                {
                   
                    unset($params['type']);
                    unset($params['visit_status']);
                    $contents = json_encode($params);
                    $where = '';
                    $updwhere = '';
                    if(isset($params['id'])){
                        $id = $params['id'];
                        $where = 'and id = '.$id;
                        $updwhere .=' and id = ?';
                        $value = $id;
                    }
                    else{
                        $msg = 'please provide visit id';
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }
                    
                    
                    $query = $db->prepare('select * from ' . $table . ' v  where status=1 '.$where);
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {
                        // $contents = json_encode($params);
                        $row = $query->fetch(PDO::FETCH_ASSOC);
                        $idd = $row['id']; 
                        $d1 = $db->prepare('Insert into visits_history (id,meta_key,meta_value) values (?,?,?)');
                        $r1 = $d1->execute([$idd,$param_type, $contents]);
                        $stmt = $db->prepare("UPDATE  $table  SET meta_value =? where meta_key = ?  ".$updwhere);
                        $stmt->execute([$contents, $param_type,$value ]);
                        $database->closeConnection();
                        $msg = 'entity updated';
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        return json_encode($res);

                    }
                    else
                    {
                        $msg = "no entity found against id # " . $id;
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }

                }
                
                if ($params['type'] == 'delvisit')
                {
                    $where = '';
                    $updwhere = '';
                    if(isset($params['id'])){
                        $id = $params['id'];
                        $where .= 'and id in ('.$id.')';
                    }
                    // if(isset($params['except'])){
                    //     $column = $params['except'];
                    //     $field = $params[$column];
                    //     $where .= 'and '.$column.' not in ('.$field.')';
                    // }
                    
                    // print_r($where);
                    // exit();
                    $query = $db->prepare('select id, JSON_SET(v.meta_value, "$.id", v.id,"$.visit_status", v.status) as meta_value from ' . $table . ' v  where status=1 '.$where);
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {
                        
                        $stmt = $db->prepare("UPDATE $table SET status = ? where id = ?");
                        $stmt->execute([0, $id]);
                        $database->closeConnection();
                        $msg = "Entity deactivated";
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        return json_encode($res);

                    }
                    else
                    {
                        $msg = "Entity not found";
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }
                }
                
            break;
            case "caregivers":

                $database = new Connection();
                $db = $database->openConnection();

                $table = 'caregivers';
                if ($params['type'] == 'getcaregivers')
                {
                    $query = $db->prepare('select c.id,c.status,c.datetime,JSON_SET(c.meta_value, "$.id", c.id,"$.member_status", c.status) as meta_value  from ' . $table . ' as c where status=1 ');
                    $query->execute();
                    $count = $query->rowCount();
                    $rows = [];
                    $msg = 'fetched total ' . $count . ' caregivers';
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
                        'data' => $rows
                    );

                    return json_encode($res);
                }
               if ($params['type'] == 'postcaregivers') {
                        unset($params['type']);
                        $contents = json_encode($params);
                    
                        // Trim whitespace from the JSON string
                        $contents = trim($contents);
                    
                        // Prepare and execute insert query using placeholders
                        $dm = $db->prepare('INSERT INTO ' . $table . ' (meta_key, meta_value, endpoint) VALUES (?, ?, ?)');
                        $rm = $dm->execute(['caregivers', $contents, 'addcaregivers']);
                    
                        if ($rm) {
                            $msg = 'caregiver posted';
                            $res = array(
                                'result' => 'success',
                                'message' => $msg
                            );
                        } else {
                            // Handle insert failure
                            $msg = 'Failed to post caregiver';
                            $res = array(
                                'result' => 'error',
                                'message' => $msg
                            );
                        }
                        return json_encode($res);
                    }
                    
                    if ($params['type'] == 'updatecaregivers') {
                        $caregiver_id = $params['id'];
                        $member_status = $params['status'];
                    
                        // Prepare and execute select query to check if caregiver exists
                        $query = $db->prepare("SELECT * FROM $table WHERE id = ?");
                        $query->execute([$caregiver_id]);
                        $count = $query->rowCount();
                    
                        if ($count > 0) {
                            unset($params['type']);
                            unset($params['status']);
                            $contents = json_encode($params);
                    
                            // Trim whitespace from the JSON string
                            $contents = trim($contents);
                    
                            // Prepare and execute update query using placeholders
                            $stmt = $db->prepare("UPDATE $table SET status = ?, meta_value = ? WHERE id = ?");
                            $updateResult = $stmt->execute([$member_status, $contents, $caregiver_id]);
                    
                            if ($updateResult) {
                                $msg = 'caregiver updated';
                                $res = array(
                                    'result' => 'success',
                                    'message' => $msg
                                );
                            } else {
                                // Handle update failure
                                $msg = 'Failed to update caregiver';
                                $res = array(
                                    'result' => 'error',
                                    'message' => $msg
                                );
                            }
                        } else {
                            // Handle no caregiver found
                            $msg = "No caregiver found against provided id";
                            $res = array(
                                'result' => 'error',
                                'message' => $msg
                            );
                        }
                        return json_encode($res);
                    }

                }

                if ($params['type'] == 'deactivatecaregivers')
                {

                    $caregiver_id = $params['id'];
                    $query = $db->prepare("select * from $table where id ='$caregiver_id'");
                    $query->execute();
                    $count = $query->rowCount();
                    if ($query->rowCount() > 0)
                    {
                        // $contents = json_encode($params);
                        $stmt = $db->prepare("UPDATE  $table  SET status = ? where id = ?");
                        $stmt->execute([0, $caregiver_id]);
                        $database->closeConnection();
                        $msg = 'caregiver deactivated';
                        $res = array(
                            'result' => 'success',
                            'message' => $msg
                        );
                        return json_encode($res);

                    }
                    else
                    {
                        $msg = "no caregiver found against provided id";
                        $res = array(
                            'result' => 'error',
                            'message' => $msg
                        );
                        //$this->logger('email',$params['body'],$res,'log',$params['to'],'thegiftingmindset@gmail.com');
                        return json_encode($res);
                    }
                }

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
    function outputCSV($data)
    {
        header("Content-Type: text/csv");
        header("Content-Disposition: attachment; filename=file.csv");
        $output = fopen("php://output", "wb");
        foreach ($data as $row) fputcsv($output, $row); // here you can change delimiter/enclosure
        fclose($output);
    }
    function fputcsv_eol($fp, $array, $eol)
    {
        $csvWriter = new yidas\csv\Writer($fp, [
        // 'quoteAll' => true,
        // 'encoding' => 'UTF-8'
        ]);
        foreach ($array as $row)
        {
            $csvWriter->writeRow($row);
            // fputcsv($fp, $row,$separator=',',$enclosure='"');
            if ("\n" != $eol && 0 === fseek($fp, -1, SEEK_CUR))
            {
                fwrite($fp, $eol);
            }
        }
    }
    function push(&$data, $item)
    {
        $quote = chr(34); // quote " character from ASCII table
        $data[] = $quote . addslashes(strval($item)) . $quote;
    }
    private function emailer($msg, $subject, $emailto)
    {
        $mail = new PHPMailer(true);
        try
        {

            //Server settings
            //$mail->SMTPDebug = SMTP::DEBUG_SERVER; //Enable verbose debug output
            $mail->isSMTP(); //Send using SMTP
            $mail->Host = 'smtp.gmail.com'; //Set the SMTP server to send through
            $mail->SMTPAuth = true; //Enable SMTP authentication
            $mail->Username = 'empirehomecareagency@gmail.com'; //SMTP username
            $mail->Password = 'svgrqkrrpdmobxim'; //SMTP password
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS; //Enable implicit TLS encryption
            $mail->Port = 587; //TCP port to connect to; use 587 if you have set `SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS`
            //Recipients
            $mail->setFrom('empirehomecareagency@gmail.com', 'Empire Homecare Agency LLC');
            $mail->addAddress($emailto); //Add a recipient
            // $mail->addAddress('ellen@example.com'); //Name is optional
            // $mail->addReplyTo('info@example.com', 'Information');
            // $mail->addCC('cc@example.com');
            $mail->addBCC('7324878977@tmomail.net');

            //Attachments
            // $mail->addAttachment('/var/tmp/file.tar.gz'); //Add attachments
            // $mail->addAttachment('/tmp/image.jpg', 'new.jpg'); //Optional name
            //Content
            $mail->isHTML(true); //Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $msg;
            $mail->AltBody = $msg;
            $mail->send();

            $res = array(
                'result' => 'success',
                'message' => 'email sent'
            );

            //$this->logger('sms', $params['body'], $res, 'log', $params['to'], 'thegiftingmindset@gmail.com');
            // return json_encode($res);
            
        }
        catch(Exception $e)
        {

            $res = array(
                'result' => 'error',
                'error' => $mail->ErrorInfo
            );

            //$this->logger('sms', $params['body'], $res, 'log', $params['to'], 'thegiftingmindset@gmail.com');
            // return json_encode($res);
            
        }
    }
    private function generateRandomString($length = 25) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
        
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

