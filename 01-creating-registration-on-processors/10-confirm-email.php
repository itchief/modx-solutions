<?php

require_once '../../config.core.php';

require_once MODX_CORE_PATH . 'model/modx/modx.class.php';
$modx = new modX();
$modx->initialize('web');
$modx->getService('error', 'error.modError', '', '');

$email = $_GET['email'];
$hash = $_GET['hash'];

if (empty($email) || empty($hash)) {
  $id = $modx->getOption('error_page');
  $redirect = $modx->makeUrl($id);
  $modx->sendRedirect($redirect);
} else {
  $profile = $modx->getObject('modUserProfile', ['email' => rawurldecode($email)]);
  if (!$profile) {
    $id = $modx->getOption('error_page');
    $redirect = $modx->makeUrl($id);
    $modx->sendRedirect($redirect);
  }
  $user = $profile->getOne('User');
  $extended = $profile->get('extended');
  $activationKey = $extended['activation_key'];
  if ($hash != $activationKey) {
    $id = $modx->getOption('error_page');
    $redirect = $modx->makeUrl($id);
    $modx->sendRedirect($redirect);
  }
  $extended = $profile->get('extended');
  unset($extended['activation_key']);
  $profile->set('extended', $extended);
  $user->set('active', 1);

  $profile->save();
  $user->save();

  // редирект на id = 1
  $id = $modx->getOption('site_start');
  $redirect = $modx->makeUrl($id);
  $modx->sendRedirect($redirect);
}
