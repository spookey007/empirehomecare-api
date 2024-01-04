<?php
  header("Content-type:application/json");
  date_default_timezone_set('Asia/karachi');
  $postdata = file_get_contents("php://input");
  $value = json_decode($postdata);
  $fw = fopen('logs/'.$value->cn.'-log-'.date("Y-m-d-H-i-s").'.txt', 'w');
  fwrite($fw, $postdata);
  fclose($fw);
  echo true;
?>