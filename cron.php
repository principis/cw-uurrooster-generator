<?php

require __DIR__.'/vendor/autoload.php';
require __DIR__.'/config/config.php';


info("Retrieving courses...");
$urls = retrieve_courses();

if (empty($urls)) {
    error('Failed to retrieve courses');
}

info("Downloading ics files...");
download_ics($urls);

info("Updating database...");
update_db($urls);

function update_db(array $icsUrls)
{
    $db = new PDO("sqlite:".__DIR__."/roosters_new.db");
    $db->exec(
        "CREATE TABLE courses(id VARCHAR PRIMARY KEY, `file` VARCHAR, `course` VARCHAR, `opo` VARCHAR, `group` VARCHAR)"
    );

    $db->beginTransaction();

    $stmt = $db->prepare("INSERT INTO courses (id, `file`,`course`,`opo`,`group`) VALUES(?,?,?,?,?)");

    foreach ($icsUrls as $key => $item) {
        $stmt->execute([substr(md5($key), 0, 8), $key, $item['course'], $item['opo'], $item['group']]);
    }
    $db->commit();
    rename(__DIR__.'/roosters_new.db', __DIR__.'/roosters.db');
}

function download_ics(array $icsUrls)
{
    $cmh = curl_multi_init();
    $file_pointers = [];
    $curl_handles = [];

    $key = -1;
    foreach ($icsUrls as $id => $item) {
        $key++;
        $file = __DIR__.'/var/cache/'.$id;
        $curl_handles[$key] = curl_init($item['url']);
        $file_pointers[$key] = fopen($file, "w");
        curl_setopt($curl_handles[$key], CURLOPT_FILE, $file_pointers[$key]);
        curl_setopt($curl_handles[$key], CURLOPT_HEADER, 0);
        curl_setopt($curl_handles[$key], CURLOPT_CONNECTTIMEOUT, 60);
        curl_multi_add_handle($cmh, $curl_handles[$key]);
    }

    // Download the files
    do {
        $status = curl_multi_exec($cmh, $running);

        if ($running) {
            curl_multi_select($cmh);
        }
    } while ($running && $status == CURLM_OK);

    // Free up objects
    for ($i = 0, $iMax = count($curl_handles); $i < $iMax; $i++) {
        curl_multi_remove_handle($cmh, $curl_handles[$i]);
        curl_close($curl_handles[$i]);
        fclose($file_pointers[$i]);
    }

    $err = curl_multi_errno($cmh);
    if ($err != 0) {
        error("curl error: $err");
    }

    curl_multi_close($cmh);
}


function retrieve_courses() :array
{
    $icsUrls = [];

    foreach (sources() as $source) {
        $base = get_base_url($source);

        $xml = new DOMDocument();
        libxml_use_internal_errors(true);
        if (!$xml->loadHTMLFile($source)) {
            error("Failed to parse html from source={$source}");
        }

        $icalDiv = $xml->getElementById('ical');
        if ($icalDiv === null) {
            error("Couldn't find #ical div, source={$source}");
        }

        /** @var DOMElement $item */
        foreach ($icalDiv->getElementsByTagName('a') as $item) {
            $course = $item->getAttribute('data-coursename');
            $opo = $item->getAttribute('data-opo');
            $group = $item->getAttribute('data-coursegroup');
            $ics = $item->getAttribute('href');

            if ($course === '' || $opo === '' || $ics === '') {
                warn("Invalid course".PHP_EOL.print_r($item, true));
                continue;
            }
            $icsUrls[basename($ics)] = [
                'course' => $course,
                'opo' => $opo,
                'group' => $group,
                'url' => $base.$ics,
            ];
        }
    }

    return $icsUrls;
}

function get_base_url(string $url) :string
{
    $rawPath = parse_url($url, PHP_URL_PATH);
    if (substr($rawPath, -1) === '/') {
        $path = $rawPath;
    } else {
        $path = dirname($rawPath).'/';
    }

    if ($path === "") {
        $path = "/";
    }

    return parse_url($url, PHP_URL_SCHEME).'://'.parse_url($url, PHP_URL_HOST).$path;
}

function error(string $msg)
{
    echo sprintf("[ERRO] %s%s", $msg, PHP_EOL);
    die(1);
}

function warn(string $msg)
{
    echo sprintf("[WARN] %s%s", $msg, PHP_EOL);
}

function info(string $msg)
{
    //echo sprintf("[INFO] %s%s", $msg, PHP_EOL);
}
