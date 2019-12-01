<?php
require 'vendor/autoload.php';

use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Slack\Extensions\Menu;
use BotMan\Drivers\Slack\SlackRTMDriver;
use React\EventLoop\Factory;
use Symfony\Component\Dotenv\Dotenv;

//Nustatymai
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');
$firstname = "";

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

function validUser($user)
{ return true; }

function sendQuestion($botman, $user_data)
{
    $question = "Test";
    $options = ['Option 1', 'Option 2'];

    $question = Question::create($question)
        ->callbackId($question);

    foreach ($options as $option) {
        $question->addButtons([Button::create($option)->value($option)]);
    };

    foreach ($user_data as $user)
    {
        $botman->say($question, $user['id']);
    }
}

//sendQuestion($botman, $user_data);

//Apklausos funckija
$botman->hears('Send poll', function ($bot) {
    $link = "http://127.0.0.1:8000/api/poll/1";
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
                $this->firstname = $response->getText();

            }
        });
    }
});


//Apklausa per hookus
$botman->hears('hook', function ($bot) {
    $urls = ['https://hooks.slack.com/services/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'];
//        "https://hooks.slack.com/services/TPVCUHMLZ/  BQQJQPYGN/wEKfm9tAB1WZzu7vPN7llMBt",
//        "https://hooks.slack.com/services/TPVCUHMLZ/  BQ9JTDMK4/lLoWPuQVVkTivH0WaOCtCYB7",
//        "https://hooks.slack.com/services/TPVCUHMLZ/  BQEERC8D7/Bi1uOQc7fgyfuYi5SHh2aXVv"];


    $channel = '#general';
    $bot_name = 'Webhook';
    $icon = ':alien:';
    $message = 'Your message';
    $attachments = array([
        "blocks" => [
            [
                "type" => "section",
                "text" => [
                    "type" => "mrkdwn",
                    "text" => "Kaip siandien jauciates?"
                ],
                "accessory" => [
                    "type" => "static_select",
                    "placeholder" => [
                        "type" => "plain_text",
                        "text" => "Select an item",
                        "emoji" => true
                    ],
                    "options" => [
                        [
                            "text" => [
                                "type" => "plain_text",
                                "text" => "Puikiai",
                                "emoji" => true
                            ],
                            "value" => "value-2"
                        ],
                        [
                            "text" => [
                                "type" => "plain_text",
                                "text" => "Gerai",
                                "emoji" => true
                            ],
                            "value" => "value-2"
                        ]
                    ]
                ]
            ]
        ]

    ]);
    $data = array(
        'channel' => $channel,
        'username' => $bot_name,
        'text' => $attachments,
        'icon_emoji' => $icon,
        'attachments' => $attachments
    );
    foreach ($urls as $url) {
        $data_string = json_encode($data);
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data_string))
        );
        //Execute CURL
        $result = curl_exec($ch);
    }

    return $result;

});


//$botman->hears('test', function ($botman) {
//    $question = Question::create('Would you like to play a game?')
//        ->callbackId('game_selection')
//        ->addAction(
//            Menu::create('Pick a game...')
//                ->name('games_list')
//                ->options([
//                    [
//                        'text' => 'Hearts',
//                        'value' => 'hearts',
//                    ],
//                    [
//                        'text' => 'Bridge',
//                        'value' => 'bridge',
//                    ],
//                    [
//                        'text' => 'Poker',
//                        'value' => 'poker',
//                    ]
//                ])
//        );
//
//    $botman->ask($question, function (Answer $answer) {
//        $selectedOptions = $answer->getValue();
//
//    });
//    $url = 'http://127.0.0.1:8000/api/store_answer';
//    $data = array('value' => "asd", 'question' => 1, 'responder'=>1);
//
//// use key 'http' even if you send the request to https://...
//    $options = array(
//        'http' => array(
//            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
//            'method'  => 'POST',
//            'content' => http_build_query($data)
//        )
//    );
//    $context  = stream_context_create($options);
//    $result = file_get_contents($url, false, $context);
//    if ($result === FALSE) { /* Handle error */ }
//
//    var_dump($result);
//});


//$botman->say('HELLO MA FRIEND', 'UPQGLUEMQ');
$loop->run();

?>

