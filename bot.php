<?php
require 'vendor/autoload.php';

use App\Bot\SlackBot;
use Symfony\Component\Dotenv\Dotenv;

/// Example
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');
$bot = new SlackBot($_ENV["BOT_TOKEN"]);

$bot->getTriggerFromAdminPanel($bot);
$bot->getTriggerFromSlackDM($bot);

$bot->loop->run();


