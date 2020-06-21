<?php
require_once __DIR__."/vendor/autoload.php";

echo "\e[94m Vk Dialog Scanner \e[0m" . PHP_EOL;
$version = "5.110";

echo "\e[32m Please, input access token: \e[0m" . PHP_EOL;
$access_token = readline("> ");

$array = [];
$vk = new VK\Client\VKApiClient($version);
$offset = 0;
$count = 200;
do {
    $params = [
        "offset" => $offset,
        "count" => 200,
        "filter" => "all",
        "extended" => 1,
        "fields" => "sex,home_town,about,books,connections,education,military,music,country,city,bdate,photo_max_orig,personal,relation,schools,universities"
    ];
    try {
        $response = $vk->messages()->getConversations($access_token, $params);
    } catch (\VK\Exceptions\Api\VKApiMessagesChatUserNoAccessException $e) {
        echo "\e[91m Error! Messages chat user no access!\e[0m";
        exit(200);
    } catch (\VK\Exceptions\Api\VKApiMessagesContactNotFoundException $e) {
        echo "\e[91m Error! Messages contact not found!\e[0m";
        exit(200);
    } catch (\VK\Exceptions\VKApiException $e) {
        echo "\e[91m Error! Wrong access token!\e[0m";
        exit(200);
    } catch (\VK\Exceptions\VKClientException $e) {
        echo "\e[91m Error! No network connection\e[0m";
        exit(200);
    }
    $count_all= $response["count"];
    if ($offset == 0) {
        echo "\e[32m ".$count_all." unique dialogs found \e[0m".PHP_EOL;
        echo "\e[32m Start scanning... \e[0m" . PHP_EOL;

    }
    if ($response["profiles"] !== null) {
        $array = array_merge($array, $response["profiles"]);
    }
    echo "\e[94m Scanned: ".count($array)." out of ".$count_all."\e[0m" . PHP_EOL;
    $count = (($count_all-$offset)<200)? ($count_all-$offset): 200;
    $offset = $offset+200;

} while ($offset<$count_all);
echo "\e[32m Scan completed!\e[0m" . PHP_EOL;
echo "\e[32m Collected data  by ".count($array)." users!\e[0m" . PHP_EOL;

$setting = json_decode(file_get_contents(__DIR__."/config/config.json"), true);
$setting["number"]++;
file_put_contents(__DIR__."/config/config.json", json_encode($setting));
$path = __DIR__."/save/".$setting["number"]."_vk_dialog_scanner.json";
file_put_contents($path ,json_encode($array));
echo "\e[32m Saved data in a file: ".$path."\e[0m" . PHP_EOL;
exit();