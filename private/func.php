<?php

function session_hash($login, $passwd){
  global $conf;
  return hash($conf['hash_algo'], $login.sha1($passwd));
}

function _message($key, $err = 'ok'){
	global $var;
	die(json_encode(['err' => $err, 'mes' => $var['error'][$key], 'key' => $key]));
}

function _exit(){
  global $var;
  if(session_status() != PHP_SESSION_NONE){
    session_unset();
    session_destroy();
    _message('success');
  }
}

function getPublic($path){
  global $user;
  if ($path == '/') {
    if ($user) {
      $path = 'students.html';
    } else {
      $path = 'index.html';
    }
  }
  $file = $_SERVER['DOCUMENT_ROOT']."/public/$path";
  if(!file_exists($file)){
    return ['err' => true, 'mes' => 'File not exists'];
  }
  return file_get_contents($file);
}

function login(){
  global $db, $user;
  if($user){
    _message('authorized', 'error');
  }
  if(empty($_POST['login']) || empty($_POST['passwd'])){
    _message('empty', 'error');
  }
  $_POST['login'] = mb_strtolower($_POST['login']);
  $query = $db->prepare('SELECT `id`, `login`, `passwd` FROM `api_users` WHERE `login` = :login');
  $query->bindValue(':login', $_POST['login']);
  $query->execute();
  if($query->rowCount() == 0) {
    _message('invalidUser', 'error');
  }
  $row = $query->fetch();

  if(!password_verify($_POST['passwd'], $row['passwd'])){
    _message('wrongPasswd', 'error');
  }
  startSession($row);
  _message('success');
}

function registration() {
  global $db;
  if(empty($_POST['login']) || empty($_POST['passwd'])){
		_message('empty', 'error');
	}
  $query = $db->prepare('SELECT `id` FROM `api_users` WHERE `login` = :login');
	$query->bindValue(':login', $_POST['login']);
	$query->execute();
	if($query->rowCount() > 0){
		_message('registered', 'error');
	}
  $passwd = password_hash($_POST['passwd'], PASSWORD_DEFAULT);
  $query = $db->prepare('INSERT INTO `api_users` (`login`, `passwd`) VALUES (:login, :passwd)');
	$query->bindValue(':login', $_POST['login']);
	$query->bindParam(':passwd', $passwd);
	$query->execute();
	_message('success');
}

function startSession($row){
  $hash = session_hash($row['login'], $row['passwd']);
  $_SESSION['sess'] = $hash;
  $_SESSION['userid'] = $row['id'];
}

function auth(){
  global $db, $user;
  if(!empty($_SESSION['sess']) && !empty($_SESSION['userid'])){
    $query = $db->prepare('SELECT `id`, `login`, `passwd` FROM `api_users` WHERE `id` = :id');
    $query->bindParam(':id', $_SESSION['userid']);
    $query->execute();
    if($query->rowCount() != 1){
      _exit();
      return;
    }
    $row = $query->fetch();
    if($_SESSION['sess'] != session_hash($row['login'], $row['passwd'])){	
      _exit();
      return;
    }
    $user = [
      'id' => $row['id'], 
      'login' => $row['login'], 
      'passwd' => $row['passwd']
    ];
  }
}

function proceedStudents($page = 1){
  global $db;
  $query = $db->prepare('SELECT `id`, `login`, `name`, `group_name` FROM `students` LIMIT 5 OFFSET :from');
  $from = ($page - 1) * 5;
  $query->bindParam(':from', $from, PDO::PARAM_INT);
  $query->execute();
  $list = $query->fetchAll();
  $query = $db->prepare('SELECT COUNT(*) FROM `students`');
  $query->execute();
  $maxPage = $query->fetch()[0] / 5;
  return [ "list" => $list, "maxPage" => $maxPage ];
}

function seedStudents($count = 100){
  global $db;
  $fakerData = [
    'fisrtName' => [
      'Arnulfo', 'Aron', 'Art', 'Arthur', 'Arturo', 'Arvel', 'Arvid', 'ArvillaAryanna', 'Asa', 'Asha', 'Ashlee', 'Ashleigh', 'Ashley', 'Ashly', 'Ashlynn', 'Ashton', 'AshtynAsia', 'Assunta', 'Astrid', 
      'Athena', 'Aubree', 'Aubrey', 'Audie', 'Audra', 'Audreanne', 'AudreyAugust', 'Augusta', 'Augustine', 'Augustus', 'Aurelia', 'Aurelie', 'Aurelio', 'Aurore', 'AustenAustin', 'Austyn', 'Autumn', 
      'Ava', 'Avery', 'Avis', 'Axel', 'Ayana', 'Ayden', 'Ayla', 'Aylin', 'BabyBailee', 'Bailey', 'Barbara', 'Barney', 'Baron', 'Barrett', 'Barry', 'Bart', 'Bartholome', 'BartonBaylee', 'Beatrice', 'Beau', 
      'Beaulah', 'Bell', 'Bella', 'Belle', 'Ben', 'Benedict', 'BenjaminBennett', 'Bennie', 'Benny', 'Benton', 'Berenice', 'Bernadette', 'Bernadine', 'BernardBernardo', 'Berneice', 'Bernhard', 'Bernice', 'Bernie', 
      'Berniece', 'Bernita', 'Berry', 'BertBerta', 'Bertha', 'Bertram', 'Bertrand', 'Beryl', 'Bessie', 'Beth', 'Bethany', 'Bethel', 'BetsyBette', 'Bettie', 'Betty', 'Bettye', 'Beulah', 'Beverly', 'Bianka', 'Bill', 
      'Billie', 'Billy', 'BirdieBlair', 'Blaise', 'Blake', 'Blanca', 'Blanche', 'Blaze', 'Bo', 'Bobbie', 'Bobby', 'Bonita', 'BonnieBoris', 'Boyd', 'Brad', 'Braden', 'Bradford', 'Bradley', 'Bradly', 'Brady', 'Braeden', 
      'BrainBrandi', 'Brando', 'Brandon', 'Brandt', 'Brandy', 'Brandyn', 'Brannon', 'Branson', 'BrantBraulio', 'Braxton', 'Brayan', 'Breana', 'Breanna', 'Breanne', 'Brenda', 'Brendan', 'BrendenBrendon', 'Brenna', 
      'Brennan', 'Brennon', 'Brent', 'Bret', 'Brett', 'Bria', 'Brian', 'BrianaBrianne', 'Brice', 'Bridget', 'Bridgette', 'Bridie'
    ],
    'lastName' => [
      'Abbott', 'Abernathy', 'Abshire', 'Adams', 'Altenwerth', 'Anderson', 'Ankunding', 'ArmstrongAuer', 'Aufderhar', 'Bahringer', 'Bailey', 'Balistreri', 'Barrows', 'Bartell', 'BartolettiBarton', 
      'Bashirian', 'Batz', 'Bauch', 'Baumbach', 'Bayer', 'Beahan', 'Beatty', 'BechtelarBecker', 'Bednar', 'Beer', 'Beier', 'Berge', 'Bergnaum', 'Bergstrom', 'Bernhard', 'BernierBins', 'Blanda', 
      'Blick', 'Block', 'Bode', 'Boehm', 'Bogan', 'Bogisich', 'Borer', 'Bosco', 'BotsfordBoyer', 'Boyle', 'Bradtke', 'Brakus', 'Braun', 'Breitenberg', 'Brekke', 'Brown', 'BruenBuckridge', 'Carroll', 
      'Carter', 'Cartwright', 'Casper', 'Cassin', 'Champlin', 'ChristiansenCole', 'Collier', 'Collins', 'Conn', 'Connelly', 'Conroy', 'Considine', 'Corkery', 'CormierCorwin', 'Cremin', 'Crist', 'Crona', 
      'Cronin', 'Crooks', 'Cruickshank', 'Cummerata', 'CummingsDach', 'D`Amore', 'Daniel', 'Dare', 'Daugherty', 'Davis', 'Deckow', 'Denesik', 'Dibbert', 'DickensDicki', 'Dickinson', 'Dietrich', 'Donnelly', 
      'Dooley', 'Douglas', 'Doyle', 'DuBuque', 'DurganEbert', 'Effertz', 'Eichmann', 'Emard', 'Emmerich', 'Erdman', 'Ernser', 'Fadel', 'Fahey', 'FarrellFay', 'Feeney', 'Feest', 'Feil', 'Ferry', 'Fisher', 
      'Flatley', 'Frami', 'Franecki', 'FriesenFritsch', 'Funk', 'Gaylord', 'Gerhold', 'Gerlach', 'Gibson', 'Gislason', 'Gleason', 'GleichnerGlover', 'Goldner', 'Goodwin', 'Gorczany', 'Gottlieb', 'Goyette', 
      'Grady', 'Graham', 'GrantGreen', 'Greenfelder', 'Greenholt', 'Grimes', 'Gulgowski', 'Gusikowski', 'GutkowskiGutmann', 'Haag', 'Hackett', 'Hagenes', 'Hahn', 'Haley', 'Halvorson', 'Hamill', 'Hammes', 
      'HandHane', 'Hansen', 'Harber', 'Harris', 'Hartmann', 'Harvey', 'Hauck', 'Hayes', 'Heaney', 'HeathcoteHegmann', 'Heidenreich', 'Heller', 'Herman', 'Hermann', 'Hermiston', 'Herzog', 'HesselHettinger', 
      'Hickle', 'Hilll', 'Hills', 'Hilpert', 'Hintz', 'Hirthe', 'Hodkiewicz', 'HoegerHomenick', 'Hoppe', 'Howe', 'Howell', 'Hudson', 'Huel', 'Huels', 'Hyatt', 'Jacobi', 'JacobsJacobson', 'Jakubowski', 'Jaskolski', 
      'Jast', 'Jenkins', 'Jerde', 'Jewess', 'Johns', 'JohnsonJohnston', 'Jones', 'Kassulke', 'Kautzer', 'Keebler', 'Keeling', 'Kemmer', 'KerlukeKertzmann', 'Kessler', 'Kiehn', 'Kihn', 'Kilback', 'King', 'Kirlin', 
      'Klein', 'Kling', 'KlockoKoch', 'Koelpin', 'Koepp', 'Kohler', 'Konopelski', 'Koss', 'Kovacek', 'Kozey', 'Krajcik', 'KreigerKris', 'Kshlerin', 'Kub', 'Kuhic', 'Kuhlman', 'Kuhn', 'Kulas', 'Kunde', 'Kunze', 
      'Kuphal', 'KutchKuvalis', 'Labadie', 'Lakin', 'Lang', 'Langosh', 'Langworth', 'Larkin', 'Larson', 'LeannonLebsack', 'Ledner', 'Leffler', 'Legros', 'Lehner', 'Lemke', 'Lesch', 'Leuschke', 'Lind', 'LindgrenLittel', 
      'Little', 'Lockman', 'Lowe', 'Lubowitz', 'Lueilwitz', 'Luettgen', 'Lynch', 'MacejkovicMaggio', 'Mann', 'Mante', 'Marks', 'Marquardt', 'Marvin', 'Mayer', 'Mayert', 'McClure', 'McCulloughMcDermott', 'McGlynn', 'McKenzie', 
      'McLaughlin', 'Medhurst', 'Mertz', 'Metz', 'Miller', 'MillsMitchell', 'Moen', 'Mohr', 'Monahan', 'Moore', 'Morar', 'Morissette', 'Mosciski', 'Mraz', 'MuellerMuller', 'Murazik', 'Murphy', 'Murray', 'Nader', 'Nicolas', 
      'Nienow', 'Nikolaus', 'NitzscheNolan', 'Oberbrunner', 'O`Connell', 'O`Conner', 'O`Hara', 'O`Keefe', 'O`Kon', 'Okuneva', 'OlsonOndricka', 'O`Reilly', 'Orn', 'Ortiz', 'Osinski', 'Pacocha', 'Padberg', 'Pagac', 
      'ParisianParker', 'Paucek', 'Pfannerstill', 'Pfeffer', 'Pollich', 'Pouros', 'Powlowski', 'PredovicPrice', 'Prohaska', 'Prosacco', 'Purdy', 'Quigley', 'Quitzon', 'Rath', 'Ratke', 'Rau', 'RaynorReichel', 'Reichert', 
      'Reilly', 'Reinger', 'Rempel', 'Renner', 'Reynolds', 'Rice', 'RippinRitchie', 'Robel', 'Roberts', 'Rodriguez', 'Rogahn', 'Rohan', 'Rolfson', 'Romaguera', 'RoobRosenbaum', 'Rowe', 'Ruecker', 'Runolfsdottir', 
      'Runolfsson', 'Runte', 'Russel', 'RutherfordRyan', 'Sanford', 'Satterfield', 'Sauer', 'Sawayn', 'Schaden', 'Schaefer', 'SchambergerSchiller', 'Schimmel', 'Schinner', 'Schmeler', 'Schmidt', 'Schmitt', 'Schneider', 
      'SchoenSchowalter', 'Schroeder', 'Schulist', 'Schultz', 'Schumm', 'Schuppe', 'Schuster', 'SengerShanahan', 'Shields', 'Simonis', 'Sipes', 'Skiles', 'Smith', 'Smitham', 'Spencer', 'SpinkaSporer', 'Stamm', 'Stanton', 
      'Stark', 'Stehr', 'Steuber', 'Stiedemann', 'Stokes', 'StoltenbergStracke', 'Streich', 'Stroman', 'Strosin', 'Swaniawski', 'Swift', 'Terry', 'Thiel', 'ThompsonTillman', 'Torp', 'Torphy', 'Towne', 'Toy', 'Trantow', 
      'Tremblay', 'Treutel', 'Tromp', 'TurcotteTurner', 'Ullrich', 'Upton', 'Vandervort', 'Veum', 'Volkman', 'Von', 'VonRueden', 'WaelchiWalker', 'Walsh', 'Walter', 'Ward', 'Waters', 'Watsica', 'Weber', 'Wehner', 'Weimann', 
      'WeissnatWelch', 'West', 'White', 'Wiegand', 'Wilderman', 'Wilkinson', 'Will', 'Williamson', 'WillmsWindler', 'Wintheiser', 'Wisoky', 'Wisozk', 'Witting', 'Wiza', 'Wolf', 'Wolff', 'Wuckert', 'WunschWyman', 'Yost', 'Yundt', 
      'Zboncak', 'Zemlak', 'Ziemann', 'Zieme', 'Zulauf'
    ],
  ];
  $fakerDataFirstNameLen = count($fakerData['fisrtName']);
  $fakerDataLastNameLen = count($fakerData['lastName']);
  for ($i=0; $i < $count; $i++) {
    $iFirstName = rand(0, $fakerDataFirstNameLen - 1);
    $iLastName = rand(0, $fakerDataLastNameLen - 1);
    $query = $db->prepare('INSERT INTO `students` (`login`, `name`) VALUES (:slogin, :sname)');
    $slogin = "scftest".$i;
    $sname = $fakerData['lastName'][$iLastName]." ".$fakerData['fisrtName'][$iFirstName];
    $query->bindParam(':slogin', $slogin);
    $query->bindParam(':sname', $sname);
    $query->execute();
  }
  _message('success');
  return;
}