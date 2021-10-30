<?php

require_once 'DatabaseSession.class.php';

$session = new DatabaseSession('root', '', 'session',  'live','127.0.0.1:3306');
session_set_save_handler(array($session, 'open'),
    array($session, 'close'),
    array($session, 'read'),
    array($session, 'write'),
    array($session, 'destroy'),
    array($session, 'gc')
);

?>