<?php

require_once '../../config.core.php';
require_once MODX_CORE_PATH . 'vendor/autoload.php';
$modx = new \MODX\Revolution\modX();
$modx->initialize();

$captcha = $_COOKIE['captcha'];
unset($_COOKIE['captcha']);
setcookie('captcha', '', time() - 3600, '/', 'localhost', false, true);

$groups = [
  'Users' => [
    'usergroup' => '2', // id группы Users
    'role' => '1' // id роли Member
  ]
];

$params = [
  'username' => $_POST['email'],
  'email' => $_POST['email'],
  'passwordgenmethod' => false,
  'specifiedpassword' => $_POST['specifiedpassword'],
  'confirmpassword' => $_POST['confirmpassword'],
  'passwordnotifymethod' => 'e', // g - генерировать, else e
  'fullname' => $_POST['fullname'],
  'captcha' => crypt($_POST['captcha'], '$1$itchief$7'),
  'correctcaptcha' => $captcha,
  'extended' => ['registered' => date('Y-m-d H:i:s', time()), 'activation_key' => md5(uniqid(rand(), true))],
  'active' => false,
  'groups' => $groups
];

$result = [];
$result['success'] = true;

$response = $modx->runProcessor('custom/user/create', $params);

if ($response->isError()) {
  $result['success'] = false;
  $result['errors'] = $response->response['errors'];
  $modx->log(modX::LOG_LEVEL_ERROR, 'User create error. ' . $response->getMessage());
}

echo json_encode($result);
