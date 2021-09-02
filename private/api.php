<?php

function api() {
  $response = apiList();
  header('Content-Type: application/json');
  die(json_encode($response, JSON_PRETTY_PRINT));
}

function exitAuth(){
  header('HTTP/1.1 401 Unauthorized');
  exit;
}

function apiList(){
  
  /* Main api methods */

  function apiGetStudents($page = 1){
    global $user;
    if(!$user){
      exitAuth();
    }
    $students = proceedStudents($page);
    if (!empty($students)) {
      return $students;
    }
    return [];
  }

  if(isset($_SERVER['PATH_INFO'])) {
    switch($_SERVER['PATH_INFO']) {
      case '/auth':
        switch($_SERVER['REQUEST_METHOD']) {
          case 'POST': 
            return login();
          break;
          case 'DELETE':
            return _exit();
          break;
        }
      break;
      case '/registration':
        switch($_SERVER['REQUEST_METHOD']) {
          case 'POST': 
            return registration();
          break;
        }
      break;
      case '/users':
        $page = isset($_GET['page']) ? $_GET['page'] : 1;
        return apiGetStudents($page);
      break;
      case '/seedUsers':
        seedStudents();
      break;
    }
  }
  return ["error" => "Method Not Found"];
}
