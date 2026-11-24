<?php
/**
* Ukrainian language file for Backup module
*/
$dictionary=array(
//general
'ABOUT' => 'Про модуль',
'HELP' => 'Допомога',
'BAK_BACKUPS' => 'Резервні копії',
'BAK_SETTING_STORAGE' => 'Налаштування сховища',
'BAK_SETTING_BACKUP' => 'Налаштування копіювання',
'BAK_SETTING_NOTIFY' => 'Налаштування сповіщень',
//backups
'BAK_CREATE' => 'Створити резервну копію',
'BAK_FREESPACE' => 'Вільне місце',
'BAK_DOWNLOAD' => 'Завантажити',
//storage
'BAK_PROVIDER' => 'Сховище',
'BAK_LOCAL_PATH' => 'Локальний шлях',
'BAK_PATH' => 'Шлях',
'BAK_LOGIN' => 'Логін',
'BAK_PASSWORD' => 'Пароль',
'BAK_URL' => 'URL',
'BAK_ACCESS_CODE' => 'Код доступу',
'BAK_GET_CODE' => 'Отримати код',
'BAK_ACCOUNT' => 'Обліковий запис',
'BAK_DELETE_CONNECTION' => 'Видалити з\'єднання',
'BAK_MAX_COUNT' => 'Максимальна кількість копій',
//backup
'BAK_TEMP_FOLDER' => 'Тимчасова папка для копії',
'BAK_BACKUP_DATABASE' => 'Копіювати базу даних',
'BAK_BACKUP_FOLDERS' => 'Папки для копіювання',
'BAK_SELECT_DEFAULT' => 'Вибрати типові',
'BAK_SELECT_ALL' => 'Вибрати все',
'BAK_UNSELECT_ALL' => 'Зняти все',
//notify
'BAK_NOTIFY_SCRIPT' => 'Виконати після створення копії',
);

foreach ($dictionary as $k=>$v) {
 if (!defined('LANG_'.$k)) {
  define('LANG_'.$k, $v);
 }
}
