<?php

namespace App\Bot;

use BotMan\BotMan\BotManFactory;
use BotMan\BotMan\Drivers\DriverManager;
use BotMan\BotMan\Messages\Outgoing\Actions\Button;
use BotMan\BotMan\Messages\Outgoing\Question;
use BotMan\Drivers\Slack\SlackRTMDriver;
use React\EventLoop\Factory;

class SlackBot
{
    private $botman;
    public $loop;
    private $host;

    public function __construct($token, $host)
    {
        DriverManager::loadDriver(SlackRTMDriver::class);
        $this->loop = Factory::create();
        $this->host = $host;
        $this->botman = BotManFactory::createForRTM([
            'slack' => [
                'token' => $token,
            ],
        ], $this->loop);
    }

    public function getTriggerForTeam(): void
    {

        $this->botman->hears('team_survey: {id}', function ($bot, $id) {
            $data = $this->getSurvey($id);
            $index = 0;
            foreach ($data[1] as $question) {
                $question = $question['question'];
                $options = $data[2][$index];
                $question = Question::create($question)
                    ->callbackId($data[0]['id']);

                foreach ($options as $option) {
                    $question->addButtons([Button::create($option["value"])
                        ->value($option["id"])]);
                };
                $index++;
                $adminId = $data[0]['user'];
                $user_data = $this->getRespondersSlackIdsByAdmin($adminId);
                foreach ($user_data as $user) {
                    $bot->say($question, $user['slackId']);
                }
            }
        });
    }

    public function getTriggerForWorkspace(): void
    {
        $this->botman->hears('workspace_survey: {id}', function ($bot, $id) {
            $data = $this->getSurvey($id);
            $index = 0;

            foreach ($data[1] as $question) {
                $question = $question['question'];
                $options = $data[2][$index];
                $question = Question::create($question)
                    ->callbackId($data[0]['id']);

                foreach ($options as $option) {
                    $question->addButtons([Button::create($option["value"])
                        ->value($option["id"])]);
                };
                $index++;
                $user_data = $this->getAllUsers();
                foreach ($user_data as $user) {
                    $bot->say($question, $user['id']);
                }
            }
        });
    }

    protected function getRespondersSlackIdsByAdmin($id)
    {
        $data = [];
        try {
            $data = json_decode(file_get_contents($this->host .
                "/api/responders/$id"), true);
        } catch (\Throwable $e) {
            var_dump($e);
        }
        return $data;
    }

    protected function getSurvey(int $id)
    {
        $data = [];
        try {
            $data = json_decode(file_get_contents($this->host .
                "/api/get/full/survey/$id"), true);
        } catch (\Throwable $e) {
            var_dump($e);
        }
        return $data;
    }

    protected function getAllUsers(): array
    {
        $user_list_url = "https://slack.com/api/users.list?token=" . $_ENV["BOT_TOKEN"] . "&pretty=1";
        $user_list = json_decode(file_get_contents($user_list_url), true);
        $user_data = [];
        foreach ($user_list["members"] as $user_info) {
            if (isset($user_info["profile"]["email"])) {
                array_push($user_data, ['id' => $user_info["id"],
                    'name' => $user_info["name"], "email" => $user_info["profile"]["email"]]);
            }
        }
        return $user_data;
    }

    protected function handleData($data, $index, $surveyId)
    {
        $question = $data[$index]["question"];
        $options = $data[$index]["options"];
        $question = Question::create($question)
            ->callbackId($surveyId);

        foreach ($options as $option) {
            $question->addButtons([Button::create($option["value"])
                ->value($option["id"])]);
        };
        return $question;
    }

    public function getTriggerFromSlackDM()
    {
        $this->botman->hears('Send poll {id}', function ($bot, $id) {
            $data = $this->getPoll($id);
            $index = 0;
            foreach ($data as $question) {
                $question = $this->handleData($data, $index);
                $index++;
                $bot->typesAndWaits(1);
                $bot->say($question);
            }
        });
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
