<?php
require 'vendor/autoload.php';

use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use React\EventLoop\Factory;
use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\Drivers\Slack\SlackRTMDriver;

// Load driver
DriverManager::loadDriver(SlackRTMDriver::class);

$loop = Factory::create();
$botman = BotManFactory::createForRTM([
    'slack' => [
        'token' => 'xoxb-811436599713-809425196483-io7eN8NKqC7aXTa9DGxjv5SE',
    ],
], $loop);

$botman->hears('test', function($bot) {
    $question = Question::create('How are you')
        ->fallback('Done')
        ->callbackId('q1')
        ->addButtons([
            Button::create('Good')->value('yes'),
            Button::create('IDK')->value('idk'),
            Button::create('Not good')->value('no'),
        ]);

    $answer = '';

    $bot->ask($question, function (Answer $response, $bot) {
        $bot->say('Hi ' . $response->getText());
    });
});

// $botman->say('HELLO MA FRIEND', 'UQ3AJBYSH');

$loop->run();
?>