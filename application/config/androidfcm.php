<?php
defined('BASEPATH') or exit('No direct script access allowed');
/*
|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
|| Android Firebase Push Notification Configurations
|||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||||
 */

/*
|--------------------------------------------------------------------------
| Firebase API Key
|--------------------------------------------------------------------------
|
| The secret key for Firebase API
|
 */
// $config['key'] = 'AAAAIw4-2vA:APA91bFYoF8JBZC04LahuCjep_U1h2uKnEunyUfBWA9TNgpBGkDN8ipARek0u-GdBLyJ64wogXMly8jxWyxkHRDSUEjxCR3WLwW4cZyEwdzDI3QsIIYMXiol_xD9R8n2z7_npSgQ_fcA';//'YOUR-FCM-SERVER-KEY';
$config['key'] = 'AAAAhCudgWE:APA91bEMSdcyv-qjvmavM32rhYDdz_MWj9ZNl9nFkPkUbHwPjrBESeKiHxk-Aoqi83F_YPSpV26XP0Txf_ThGLPRh6mLySKMePV9c7xWEkZqbufHyLGwom_yNn_gWbk4GaUvblgQMo2C';//'YOUR-FCM-SERVER-KEY';

/*
|--------------------------------------------------------------------------
| Firebase Cloud Messaging API URL
|--------------------------------------------------------------------------
|
| The URL for Firebase Cloud Messafing
|
 */

$config['fcm_url'] = 'https://fcm.googleapis.com/fcm/send';
