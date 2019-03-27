<?php

define(PLATFORM_404_FILE_PATH, 'web/typo3temp/assets/platform-404/');

$createdDirectory = mkdir(PLATFORM_404_FILE_PATH, true);

if (!$createdDirectory || !file_exists(PLATFORM_404_FILE_PATH)) {
    echo 'ERROR: Could not create 404 file directory "' . PLATFORM_404_FILE_PATH . '"' . PHP_EOL;
    die(1);
}

file_put_contents(PLATFORM_404_FILE_PATH . '404.html', '<!--#echo var="HTTP_HOST"-->');
