<?php

require_once('MyLogger.php');
require_once('StorageType.php');

/*
 * storing log in file system
 */
$loggerFS = new MyLogger(__DIR__.'/logs', StorageType::FILE_SYSTEM);
$loggerFS->info('FS: This is an info log, {test} ', array('test' => 'additional information'));
$loggerFS->error('FS: This is an error log');
$loggerFS->debug('FS: This is an debug log with context.');
$loggerFS->info('FS: Placeholder example, {user} has been logged in.', array('user' => 'Passanger'));


/*
 * storing log in database
 *
 */
//$loggerDB = new MyLogger('localhost','loggerdb','root','root',StorageType::DATABASE);
//$loggerDB->info('DB: This is an info log');
//$loggerDB->error('DB: This is an error log');
//$loggerDB->debug('DB: This is an debug log with context.');
//$loggerDB->info('DB: Placeholder example, {user} has been logged in.', array('user' => 'pici'));

?>