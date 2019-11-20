<?php
if (!is_file($_SERVER['DOCUMENT_ROOT'].'/vue.sql') || !is_file($_SERVER['DOCUMENT_ROOT'].'/public/install/index.html')){
    header('Location: /public/index.html');
}else{
    header('Location: /public/install/index.html');
}







?>