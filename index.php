<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Origin, X-Requested-With, Content-Type, Accept");

defined('__INDEX_DIR__') or define('__INDEX_DIR__', __DIR__);
defined('__ROOT_DIR__') or define('__ROOT_DIR__', __DIR__);

defined('__HOST__') or define('__HOST__', $_SERVER['HTTP_HOST']);
defined('__KL_DEBUG__') or define('__KL_DEBUG__', 'yes');

error_reporting(11);
ini_set('display_errors', 'on');

require 'autoloader.php';

require 'config/env/main.php';

(new \models\Api())->run();


