<?php

namespace MODX\Revolution\Processors\Custom\User;

class Create extends \MODX\Revolution\Processors\Security\User\Create {
  public $permission = '';

  public function beforeSave() : bool {
    $captcha = $this->getProperty('captcha');
    $correctCaptcha = $this->getProperty('correctcaptcha');
    if (!$captcha) {
      $this->addFieldError('captcha', 'Пожалуйста укажите код на изображении!');
    } else if ($captcha !== $correctCaptcha) {
      $this->addFieldError('captcha', 'Этот код капчи не соответствует изображению!');
    }

    $fullname = $this->getProperty('fullname');
    if (!$fullname) {
      $this->addFieldError('fullname', 'Пожалуйста укажите имя или псевдоним!');
    } else if ($this->modx->getCount('modUserProfile', array('fullname' => $fullname)) > 0) {
      $this->addFieldError('fullname', 'Это имя уже занято!');
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
      $link = MODX_SITE_URL . 'assets/php/confirm-email.php?email=' . rawurlencode($email) . '&hash=' . $hash;
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
