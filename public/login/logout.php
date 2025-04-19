<?php
require "../../api/login/authentication.php";
session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Strict'
]);
logout();
header("Location: ../index.php");
exit();