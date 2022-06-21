<?php

require('vendor/autoload.php');

$url = isset($_GET['url']) ? $_GET['url'] : null;

function getTitle($url) {
    $page = file_get_contents($url);
    $title = preg_match('/<title[^>]*>(.*?)<\/title>/ims', $page, $match) ? $match[1] : null;
	$video_title = substr($title, 0, -10);
    return $video_title;
}

$video_name = getTitle($url);

function send_json($data)
{
    header('Content-Type: application/json');
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}

if (!$url) {
    send_json([
        'error' => 'No URL provided!'
    ]);
}

$youtube = new \YouTube\YouTubeDownloader();

try {
    $links = $youtube->getDownloadLinks($url);

    $first = $links->getFirstCombinedFormat();
	$second = $links->getSecondCombinedFormat();
	$third = $links->getThirdCombinedFormat();

    if ($first) {
        send_json([
            'links' => [$first->url, $second->url, $third->url],
			'name'  => [$video_name]
        ]);
    } else {
        send_json(['error' => 'No links found']);
    }

} catch (\YouTube\Exception\YouTubeException $e) {

    send_json([
        'error' => $e->getMessage()
    ]);
}
