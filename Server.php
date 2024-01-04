<?php
class Server
{

  public function auth($user,$pass)
  {
      $params = array(
          "user"=>$user,
          "pass"=>$pass
          );
        return json_decode(curl_v('GET',$params));
  }
}