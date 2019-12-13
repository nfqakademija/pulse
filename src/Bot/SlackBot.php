<?php

namespace App\Bot;

//require '../../vendor/autoload.php';

use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Messages\Incoming\Answer;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Slack\SlackRTMDriver;
use React\EventLoop\Factory;

class SlackBot
{
    private $botman;

    public function __construct($token)
    {
        DriverManager::loadDriver(SlackRTMDriver::class);
        $loop = Factory::create();
        $this->botman = BotManFactory::createForRTM([
            'slack' => [
                'token' => $token,
            ],
        ], $loop);
    }

    public function sendToUsers(array $users, string $base, array $options)
    {
        $question = Question::create($base)
            ->callbackId($base);

        foreach ($options as $option) {
            $question->addButtons([Button::create($option)->value($option)]);
        };

        foreach ($users as $user) {
            $this->botman->say($question, $user);
        }
    }
}

// Example
//$bot = new SlackBot("000000000000000000");
//$bot->sendToUsers(['UserId'], "How Are you", ['Yes', 'No']);