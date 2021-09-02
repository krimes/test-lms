<?php
require($_SERVER['DOCUMENT_ROOT'].'/private/config.php');
require($_SERVER['DOCUMENT_ROOT'].'/private/init/mysql.php');
require($_SERVER['DOCUMENT_ROOT'].'/private/init/session.php');
require($_SERVER['DOCUMENT_ROOT'].'/private/init/var.php');
require($_SERVER['DOCUMENT_ROOT'].'/private/func.php');
require($_SERVER['DOCUMENT_ROOT'].'/private/auth.php');
require($_SERVER['DOCUMENT_ROOT'].'/private/api.php');

if (isset($_SERVER['PATH_INFO']) && strpos($_SERVER['PATH_INFO'], '/api/') === 0){
  $_SERVER['PATH_INFO'] = substr($_SERVER['PATH_INFO'], 4);
  $_POST = json_decode(file_get_contents("php://input"), true);
  api();
}
if (!isset($_SERVER['PATH_INFO'])) {
  $_SERVER['PATH_INFO'] = '/';
}
print_r(getPublic($_SERVER['PATH_INFO']));
