<?php

error_reporting(E_ALL);
ini_set('display_errors', '1');
if ($_GET['base64']){
    $data = $_GET['base64'];
    $data = str_replace('data:image/png;base64,', '', $data);
    $data = str_replace(' ', '+', $data);
    $data = base64_decode($data);
    header('Content-type: image/png');
    echo $data;
} else {
  echo "Creating image failed.";
}

?>
