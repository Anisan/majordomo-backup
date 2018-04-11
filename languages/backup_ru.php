<?php
/**
* Default language file for Telegram module
*
*/
$dictionary=array(
//general
'ABOUT' => 'О модуле',
'HELP' => 'Помощь',
'BAK_BACKUPS' => 'Резервные копии',
'BAK_SETTING_STORAGE' => 'Настройки хранилища',
'BAK_SETTING_BACKUP' => 'Настройки резервной копии',
'BAK_SETTING_NOTIFY' => 'Настройки уведомлений',
//backups
'BAK_CREATE' => 'Создать резервную копию',
'BAK_FREESPACE' => 'Свободно',
'BAK_DOWNLOAD' => 'Скачать',
//storage
'BAK_PROVIDER' => 'Хранилище',
'BAK_LOCAL_PATH' => 'Локальная папка',
'BAK_PATH' => 'Папка',
'BAK_LOGIN' => 'Логин',
'BAK_PASSWORD' => 'Пароль',
'BAK_URL' => 'Url',
'BAK_ACCESS_CODE' => 'Код доступа',
'BAK_GET_CODE' => 'Получить код',
'BAK_ACCOUNT' => 'Аккаунт',
'BAK_DELETE_CONNECTION' => 'Удалить соединение',
'BAK_MAX_COUNT' => 'Максимально количество резервных копий',
//backup
'BAK_TEMP_FOLDER' => 'Временная папка для создания копии',
'BAK_BACKUP_DATABASE' => 'Резервировать базу данных',
'BAK_BACKUP_FOLDERS' => 'Папки резевной копии',
'BAK_SELECT_DEFAULT' => 'Выбрать по-умолчанию',
'BAK_SELECT_ALL' => 'Выбрать все',
'BAK_UNSELECT_ALL' => 'Отменить все',
//notify
'BAK_NOTIFY_SCRIPT' => 'Выполнить после создания',

/* end module names */
);

foreach ($dictionary as $k=>$v) {
 if (!defined('LANG_'.$k)) {
  define('LANG_'.$k, $v);
 }
}

?>