<?php

define('PLATFORM_404_FILE_PATH', 'web/typo3temp/assets/platform-404');

$createdDirectory = mkdir(PLATFORM_404_FILE_PATH, 02775, true);

if (!$createdDirectory || !file_exists(PLATFORM_404_FILE_PATH)) {
    echo 'ERROR: Could not create 404 file directory "' . PLATFORM_404_FILE_PATH . '"' . PHP_EOL;
    die(1);
}

chmod(PLATFORM_404_FILE_PATH, 02775);
file_put_contents(PLATFORM_404_FILE_PATH . '/' . 'test.html', PHP_EOL . 'Test is true' . PHP_EOL);
file_put_contents(PLATFORM_404_FILE_PATH . '/' . '404.html', PHP_EOL . 'Test: <!--#include virtual="test.html" -->' . PHP_EOL);
