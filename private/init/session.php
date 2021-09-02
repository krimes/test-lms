<?php

function sessionHandler(){
  $var['origin_url'] = $_SERVER['SERVER_NAME'];

  $lifetime = 60*60*24*30;
  session_set_cookie_params($lifetime, '/', $var['origin_url'], true, true);

  session_start();

}

sessionHandler();
