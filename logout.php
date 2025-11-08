<?php
require_once 'init.php';

$userModel = new User();
$userModel->logout();

redirect('/');
