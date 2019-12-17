<?php
require 'vendor/autoload.php';

use App\Bot\SlackBot;
use Symfony\Component\Dotenv\Dotenv;

/// Example
$dotenv = new Dotenv();
$dotenv->load(__DIR__ . '/.env');
$bot = new SlackBot($_ENV["BOT_TOKEN"], "http://pulse.projektai.nfqakademija.lt");

$bot->getTriggerForTeam();
$bot->getTriggerForWorkspace();

$bot->loop->run();
