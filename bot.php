<?php
require 'vendor/autoload.php';

use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Slack\Extensions\Menu;
use React\EventLoop\Factory;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Slack\SlackRTMDriver;
use Symfony\Component\Dotenv\Dotenv;

//Nustatymai
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');

// Load driver
DriverManager::loadDriver(SlackRTMDriver::class);
$loop = Factory::create();
$botman = BotManFactory::createForRTM([
    'slack' => [
        'token' => $_ENV["BOT_TOKEN"],
    ],
], $loop);

//Gaunam visus userius is workspace
$user_list_url = "https://slack.com/api/users.list?token=" . $_ENV["BOT_TOKEN"] . "&pretty=1";
$user_list = json_decode(file_get_contents($user_list_url), true);

$user_data = [];
foreach ($user_list["members"] as $user_info) {
    if (isset($user_info["profile"]["email"]))
        array_push($user_data, ['id' => $user_info["id"],
            'name' => $user_info["name"], "email" => $user_info["profile"]["email"]]);
    // Arvydas UPQGLUEMQ
    // Titas UQ3A9UJ5N
    // Kristijonas UQ3AJBYSH
    // Andrius UPVCV06U9
}

//Apklausos funckija
$botman->hears('Send poll', function ($bot) {
    $link = "http://127.0.0.1:8000/api/form/1";
    try {
        $data = json_decode(file_get_contents($link), true);
    } catch (\Throwable $e) {
        var_dump($e);
    }
    $index = 0;
    foreach ($data as $question) {
        $question = $data[$index]["question"];
        $options = $data[$index]["options"];
        $index++;
        $question = Question::create($question)
            ->callbackId($question);

        foreach ($options as $option) {
            $question->addButtons([Button::create($option["value"])->value($option["value"])]);
        };
        $bot->typesAndWaits(1);
        $bot->ask($question, function (Answer $response) {
            var_dump("send poll ask dalis");
            if ($response->isInteractiveMessageReply()) {
                $selectedValue = $response->getValue(); // will be either 'yes' or 'no'
                $selectedText = $response->getText(); // will be either 'Of course' or 'Hell no!'
                var_dump($selectedText);
            }
        });
    }
});
    //Apklausa per hookus
$botman->hears('hook', function ($bot) {
    $urls = ['https://hooks.slack.com/services/TPVCUHMLZ   /BQN05RBJQ/kTZNwoj06mTD6xq6UGvR7EIV',
    "https://hooks.slack.com/services/TPVCUHMLZ/BQQJQPYGN   /wEKfm9tAB1WZzu7vPN7llMBt",
     "https://hooks.slack.com/services/TPVCUHMLZ/BQ9JTDMK4   /lLoWPuQVVkTivH0WaOCtCYB7",
     "https://hooks.slack.com/services/TPVCUHMLZ/BQEERC8D7   /Bi1uOQc7fgyfuYi5SHh2aXVv"];

    $data =[
        "content-type"  =>  "application/json",
        "text"=> "Danny Torrence left a 1 star review for your property.",
        'blocks' =>[[
            'type' => 'mrkdwn',
            'text' => [
                "type"=>"mrkdwn",
                "text"=> "you can add a buttom"
            ]
        ]],
   ];


    foreach ($urls as $url) {
        $hookObject = json_encode(["text"=> "Testing hook",]
            , JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $hookObject,
            CURLOPT_HTTPHEADER => [
                "Length" => strlen($hookObject),
                "Content-Type" => "application/json"
            ]
        ]);

        $response = curl_exec($ch);
        curl_close($ch);
        var_dump($response);
    }
});


//
//$botman->hears('taip', function ($botman) {
//    $botman->say('HELLO MA FRIEND', 'UPQGLUEMQ');
//
//});


// $botman->say('HELLO MA FRIEND', 'UPQGLUEMQ');
$loop->run();

?>

