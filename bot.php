<?php
require 'vendor/autoload.php';

use App\Bot\SlackBot;
use Symfony\Component\Dotenv\Dotenv;

/// Example
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');
$bot = new SlackBot($_ENV["BOT_TOKEN"], "http://127.0.0.1:8000");

$bot->getTriggerForTeam();
$bot->getTriggerForWorkspace();


$bot->loop->run();
