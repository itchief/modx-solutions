<?php

require MODX_CORE_PATH . 'model/modx/processors/security/user/create.class.php';

class extUserCreateProcessor extends modUserCreateProcessor {
  public $permission = '';

  public function beforeSave() {
    $captcha = $this->getProperty('captcha');
    $correctCaptcha = $this->getProperty('correctcaptcha');
    if (!$captcha) {
      $this->addFieldError('captcha', 'Пожалуйста укажите код на изображении!');
    } else {
      if ($captcha !== $correctCaptcha) {
        $this->addFieldError('captcha', 'Этот код капчи не соответствует изображению!');
      }
    }
    $fullname = $this->getProperty('fullname');
    if (!$fullname) {
      $this->addFieldError('fullname', 'Пожалуйста укажите имя или псевдоним!');
    } else {
      if ($this->modx->getCount('modUserProfile', array('fullname' => $fullname)) > 0) {
        $this->addFieldError('fullname', 'Это имя уже занято!');
      }
    }
    return parent::beforeSave();
  }

  public function sendNotificationEmail() {
    if ($this->getProperty('passwordnotifymethod') == 'e') {
      $chunk = MODX_CORE_PATH . 'elements/chunks/_websignupemail_message.html';
      $message = file_get_contents($chunk);
      $username = $this->object->get('username');
      $fullname = $this->profile->get('fullname');
      $sitename = $this->modx->getOption('site_name');
      $email = $this->object->get('email');
      $hash = $this->profile->get('extended')['activation_key'];
      $link = $this->modx->makeUrl('3', '', array(
        'email' => rawurlencode($email),
        'hash' => $hash,
      ), 'full');
      $placeholders = array(
        'username' => $username,
        'fullname' => $fullname,
        'site_name' =>  $sitename,
        'year' => date('Y'),
        'link' => $link
      );
      foreach ($placeholders as $k => $v) {
        $message = str_replace('[[+' . $k . ']]', $v, $message);
      }
      $this->object->sendEmail($message);
    }
  }
}
return 'extUserCreateProcessor';
