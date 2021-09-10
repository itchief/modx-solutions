<?php

require_once '../../config.core.php';

require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('web');
$modx->getService('error', 'error.modError', '', '');

$captcha = $_COOKIE['captcha'];
unset($_COOKIE['captcha']);
setcookie('captcha', '', time() - 3600, '/', 'test.ru', false, true);

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
  'captcha' => $_POST['captcha'],
  'correctcaptcha' => $captcha,
  'extended' => ['registered' => date('Y-m-d H:i:s', time()), 'activation_key' => md5(uniqid(rand(), true))],
  'active' => false,
  'groups' => $groups
];

$result = [];
$result['success'] = true;

$response = $modx->runProcessor('ext/user/create', $params);

if ($response->isError()) {
  $result['success'] = false;
  $result['errors'] = $response->response['errors'];
  $modx->log(modX::LOG_LEVEL_ERROR, 'User create error. ' . $response->getMessage());
}

echo json_encode($result);
