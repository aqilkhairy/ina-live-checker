<?php
header("Content-type: application/json; charset=utf-8");

$channelId = "UCMwGHR0BTZuLsmjY_NT5Pwg"; //ina channel id
$apiKey = "API_KEY_GOES_HERE"; //API key

function fetchYouTubeData($url)
{
    global $apiKey;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'X-APIKEY: ' . $apiKey
    ));
    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        die(json_encode([
            "error" => "Error occurred while fetching data: " . curl_error($ch),
            "url" => $url
        ]));
    }

    curl_close($ch);
    return json_decode($response, true);
}

if (!$channelId || !$apiKey) {
    die(json_encode(["error" => "Missing channel_id or api_key parameter"]));
}

$baseUrl = "https://holodex.net/api/v2/";

$channelUrl = $baseUrl . "channels/{$channelId}";
$channelData = fetchYouTubeData($channelUrl);
if (empty($channelData)) {
    die(json_encode(["error" => "Channel not found"]));
}

$channelInfo = $channelData;
$response = [
    "name" => $channelInfo['name'],
    "profilePic" => $channelInfo['photo'],
    "channelLink" => "https://www.youtube.com/channel/{$channelId}",
    "isLive" => false,
    "liveStream" => null,
    "latestVideo" => null
];

$liveStreamUrl = $baseUrl . "channels/{$channelId}/videos?limit=1&order=desc&status=live";
$liveStreamData = fetchYouTubeData($liveStreamUrl);

if (!empty($liveStreamData)) {
    $liveStream = $liveStreamData[0];
    $response['isLive'] = true;
    $response['liveStream'] = [
        "title" => $liveStream['title'],
        "thumbnail" => 'https://i.ytimg.com/vi/' . $liveStream['id'] . '/hqdefault.jpg',
        "link" => "https://www.youtube.com/watch?v=" . $liveStream['id'],
        "publishedAt" => $liveStream['published_at'] ?? $liveStream['available_at']
    ];
}

$archivedStreamsUrl = $baseUrl . "channels/{$channelId}/videos?limit=1&order=desc&status=past";
$archivedStreamsData = fetchYouTubeData($archivedStreamsUrl);

if (!empty($archivedStreamsData)) {
    $response['archivedStreams'] = [];
    foreach ($archivedStreamsData as $stream) {
        $response['archivedStreams'][] = [
            "title" => $stream['title'],
            "thumbnail" => 'https://i.ytimg.com/vi/' . $stream['id'] . '/hqdefault.jpg',
            "link" => "https://www.youtube.com/watch?v=" . $stream['id'],
            "publishedAt" => $stream['available_at']
        ];
    }

    usort($response['archivedStreams'], function ($a, $b) {
        return strtotime($b['publishedAt']) - strtotime($a['publishedAt']);
    });
}

echo json_encode($response, JSON_PRETTY_PRINT);
