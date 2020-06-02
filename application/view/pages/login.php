<?php 

require_once(ROOT . '/../application/config/config.php');
require_once(ROOT . '/../application/controller/classes/user.php');

$user = new User($conn);
$user->login();

?>