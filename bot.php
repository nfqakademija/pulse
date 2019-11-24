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

$token = "";
$users = ["UQ3AJBYSH"];
$question = "How are you";
$answers = ["Good", "Average", "Low"];

$loop = Factory::create();
$botman = BotManFactory::createForRTM([
    'slack' => [
        'token' => $token,
    ],
], $loop);


$answersFormatted = [];
foreach ($answers as $answer)
{
    $answersFormatted[] = Button::create($answer)->value($answer);
}

$question = Question::create($question)
    ->fallback('Answer')
    ->callbackId('Q1')
    ->addButtons($answersFormatted);

foreach($users as $user)
{
    $botman->say($question, $user);
}

$loop->run();
?>