<?php

define('PLATFORM_404_FILE_PATH', 'web/typo3temp/assets/platform-404');
define('PLATFORM_404_404_PAGE_PATH', '/404');
define('PLATFORM_404_REQUEST_TIMEOUT', 15);

if (mb_strlen(PLATFORM_404_FILE_PATH) < 5) {
    echo 'ERROR: 404 file directory path is too short "' . PLATFORM_404_FILE_PATH . '"' . PHP_EOL;
    die(2);
}

if(!file_exists(PLATFORM_404_FILE_PATH)) {
    $createdDirectory = mkdir(PLATFORM_404_FILE_PATH, 02775, true);
} else {
    $createdDirectory = true;
}

if (!$createdDirectory || !file_exists(PLATFORM_404_FILE_PATH)) {
    echo 'ERROR: Could not create 404 file directory "' . PLATFORM_404_FILE_PATH . '"' . PHP_EOL;
    die(1);
}

chmod(PLATFORM_404_FILE_PATH, 02775);

foreach (array_diff(scandir(PLATFORM_404_FILE_PATH), array('..', '.')) as $fileName) {
    unlink(PLATFORM_404_FILE_PATH . '/' . $fileName);
}

if (!isset($_ENV['PLATFORM_ROUTES'])) {
    echo 'ERROR: No platform routes defined.' . PHP_EOL;
    die(3);
}

$platformRoutes = json_decode(base64_decode($_ENV['PLATFORM_ROUTES']), true);

$hostDomains = [];
foreach ($platformRoutes as $route => $routeConfiguration) {
    $hostDomain = parse_url($route, PHP_URL_HOST);

    if ($routeConfiguration['type'] !== 'redirect' && !in_array($hostDomain, $hostDomains)) {
        $hostDomains[] = $hostDomain;
    }
}

for ($i=0; $i < count($hostDomains); $i++) {
    $hostDomain = $hostDomains[$i];

    $curl = curl_init('https://' . $hostDomain . PLATFORM_404_404_PAGE_PATH);

    curl_setopt($curl, CURLOPT_HEADER, false);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
    curl_setopt($curl, PLATFORM_404_REQUEST_TIMEOUT, 15);
    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Cache-Control: no-cache'));

    $returnData = curl_exec($curl);

    if ($returnData === false) {
        unset($hostDomains[$i]);
        continue;
    }

    file_put_contents(
        PLATFORM_404_FILE_PATH . '/' /*. str_replace('.', '_', $hostDomain)*/ . 'test.html',
        $returnData . '<!-- 404 fetched ' . date('c') . ' -->'
    );

    curl_close($curl);
}

$out = '';
for ($i=0; $i < count($hostDomains); $i++) {
    $hostDomain = $hostDomains[$i];

    if ($i === 0) {
        //$out .= '<!--# if expr="$HTTP_HOST = ' . $hostDomain . '" -->' . PHP_EOL;
    } else {
        //$out .= '<!--# elif expr="$HTTP_HOST = ' . $hostDomain . '" -->' . PHP_EOL;
    }

    $out .= '<!--# include file="' /*. str_replace('.', '_', $hostDomain)*/ . 'test.html" -->';
}

/*$out .= '<!--# else -->
<!DOCTYPE html>
<html>
<head>
	<meta charset="utf-8" />
	<title>404 - Page Not Found</title>
</head>
<body>
    <h1>404 - Page Not Found</h1>
    <p>The page you requested could not be found.</p>
</body>
</html>
<!--# endif -->';*/

file_put_contents(PLATFORM_404_FILE_PATH . '/' . '404.html', iconv("UTF-8","ASCII//TRANSLIT", $out));
