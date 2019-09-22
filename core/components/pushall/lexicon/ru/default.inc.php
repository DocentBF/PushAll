<?php
include_once 'setting.inc.php';

$_lang['pushall'] = 'PushAll';


$_lang['pushall_err_settings_ns'] = 'Проверьте настройки PushAll. Ключ или ID канала не заполнен.';
$_lang['pushall_err_title_ns'] = 'Пустой заголовок сообщения';
$_lang['pushall_err_text_ns'] = 'Пустоe сообщениe';

$_lang['pushall_title'] = 'Заголовок Push уведомления (если пусто - используется заголовок записи)';
$_lang['pushall_text'] = 'Основной текст Push уведомления (если пусто - используется содержимое записи)';
$_lang['pushall_send'] = 'Отправить Push уведомление?';

$_lang['pushall_neworder_title'] = 'На сайте [[++site_name]] новый заказ';
$_lang['pushall_neworder_text'] = 'Заказ №[[+num]] стоимостью [[+cost]] [[%ms2_frontend_currency? &namespace=`minishop2` &topic=`default`]] <br>Нажмите, чтобы посмотреть заказ';