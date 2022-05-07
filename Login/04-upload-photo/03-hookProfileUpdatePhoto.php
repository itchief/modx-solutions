<?php
// разрешённые MIME типы
$allowedMimeTypes = ['jpg' => 'image/jpeg', 'png' => 'image/png'];
// директория для файлов
$path = $modx->config['base_path'] . 'assets/img-users/';
// параметры для создания уменьшенной копии изображения
$params = ['w' => 100, 'h' => 100, 'zc' => 1, 'q' => 80];

// получим профиль пользователя
$profile = $modx->user->getOne('Profile');

// если глобальный массив POST содержит ключ remove-photo
if (isset($_POST['remove-photo'])) {
  // если поле photo не пустое, то..
  if ($profile->get('photo')) {
    // полный имя файла
    $fileFullName = $modx->config['base_path'] . $profile->get('photo');
    // удалим файл
    if (file_exists($fileFullName)) {
      unlink($fileFullName);
    }
    // установим полю photo пустую строку
    $hook->setValue('photo', '');
  }
  return true;
}

// если upload не является файлом
if ($_FILES['photo']['error'] === UPLOAD_ERR_NO_FILE) {
  // установим полю photo текущее значение
  $hook->setValue('photo', $profile->get('photo'));
  return true;
}

// файл, принятый сервером
$fileTmp = $_FILES['photo']['tmp_name'];

// если файл загружен успешно, то
if (!is_uploaded_file($fileTmp) || $_FILES['photo']['error'] !== UPLOAD_ERR_OK) {
  $hook->addError('photo', 'Ошибка при загрузке изображения.');
  return false;
}

// расширение файла
$finfo = new finfo(FILEINFO_MIME_TYPE);
$fileMimeType = $finfo->file($fileTmp);
$fileExt = array_search($fileMimeType, $allowedMimeTypes, true);
if ($fileExt === false) {
  $hook->addError('photo', 'Файл имеет не допустимый формат.');
  return false;
}
// добавим хеш и расширение
$fileName = uniqid() . '.' . $fileExt;
// полное имя файла
$fileFullName = $path . $fileName;
// копируем файл
copy($fileTmp, $fileFullName);
// имя и путь для профиля, поскольку они отличаются из-за медиа-ресурса
$hook->setValue('photo', $modx->getOption('assets_url') . 'img-users/' . $fileName);

// подключим phpthumb.class.php
require_once MODX_CORE_PATH . 'vendor/james-heinrich/phpthumb/phpthumb.class.php';
// создадим новый экземпляр класса phpThumb
$phpThumb = new phpThumb();
// указываем исходное изображение
$phpThumb->setSourceFilename($fileFullName);
// устанавливаем параметры
foreach($params as $key => $value) {
  $phpThumb->setParameter($key, $value);
}
// генерируем уменьшенное изображение
if ($phpThumb->GenerateThumbnail()) {
  // сохраняем изображение в файл $fullNameFile
  if (!$phpThumb->RenderToFile($fileFullName)) {
    $modx->log(modX::LOG_LEVEL_ERROR, 'Ошибка при сохранении уменьшенного изображения в файл ' . $fileFullName);
  }
} else {
  $modx->log(modX::LOG_LEVEL_ERROR, print_r($phpThumb->debugmessages, 1));
}

return true;
