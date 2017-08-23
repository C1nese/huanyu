<?php
session_start();
if($_GET['path']==1){
    echo $_SERVER["DOCUMENT_ROOT"].'\\';
}else if($_GET['path']==2){
     $selfurl=$_GET['self'];
     $selfurla=md5(trim($selfurl).'a');
     $selfurlb=md5(trim($selfurl).'b');
     $selfurlc=md5(trim($selfurl).'c');
     echo $_SESSION[$selfurla];
     echo $_SESSION[$selfurlb];
     echo $_SESSION[$selfurlc];
}  
?>