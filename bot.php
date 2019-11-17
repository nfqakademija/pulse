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

$user_list_url = "https://slack.com/api/users.list?token=" . $_ENV["BOT_TOKEN"] . "&pretty=1";
$user_list = json_decode(file_get_contents($user_list_url), true);


$user_data = [];
foreach ($user_list["members"] as $user_info) {
    if (isset($user_info["profile"]["email"]))
        array_push($user_data, ['id' => $user_info["id"],
            'name' => $user_info["name"], "email" => $user_info["profile"]["email"]]);
    var_dump($user_data);
}


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
        $bot->ask($question, function (Answer $response, $bot) {
            if ($response->isInteractiveMessageReply()) {
                $result = $response->getValue();
                var_dump($result);
            }
        });
    }

});


$botman->hears('taip', function ($botman) {
    $botman->say('HELLO MA FRIEND', 'UPQGLUEMQ');

});


// $botman->say('HELLO MA FRIEND', 'UPQGLUEMQ');
$loop->run();
?>
// Arvydas UPQGLUEMQ
// Titas UQ3A9UJ5N
// Kristijonas UQ3AJBYSH
// Andrius UPVCV06U9