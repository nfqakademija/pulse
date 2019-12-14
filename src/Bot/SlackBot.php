<?php

namespace App\Bot;

// require '../../vendor/autoload.php';

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

    public function getTriggerForTeam(): void
    {
        $this->botman->hears('team_poll: {id}', function ($bot, $id) {
            $data = $this->getPoll($id);
            $index = 0;
            foreach ($data as $question) {
                $question = $this->handleData($data, $index);
                $index++;
                $adminId = $this->getAdminIdWhoSendPoll($id);
                $user_data = $this->getRespondersSlackIdsByAdmin($adminId);
                foreach ($user_data as $user) {
                    $bot->say($question, $user['slackId']);
                }
            }
        });
    }

    public function getTriggerForWorkspace(): void
    {
        $this->botman->hears('workspace_poll: {id}', function ($bot, $id) {
            $data = $this->getPoll($id);
            $index = 0;
            foreach ($data as $question) {
                $question = $this->handleData($data, $index);
                $index++;
                $user_data = $this->getAllUsers();
                foreach ($user_data as $user) {
                    $bot->say($question, $user['id']);
                }
            }
        });
    }

    protected function getAdminIdWhoSendPoll($id)
    {
        $data = [];
        try {
            $data = json_decode(file_get_contents($this->host .
                "/api/poll/$id"), true);
        } catch (\Throwable $e) {
            var_dump($e);
        }
        return $data[0]['user'];
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

    protected function getPoll(int $id)
    {
        $data = [];
        try {
            $data = json_decode(file_get_contents($this->host .
                "/api/get/full/poll/$id"), true);
        } catch (\Throwable $e) {
            var_dump($e);
        }
        return $data;
    }

    protected function handleData($data, $index)
    {
        $question = $data[$index]["question"];
        $options = $data[$index]["options"];
        $question = Question::create($question)
            ->callbackId($question);

        foreach ($options as $option) {
            $question->addButtons([Button::create($option["value"])
                ->value($option["value"])]);
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
                $bot->ask($question, function (Answer $response) {
//                    if ($response->isInteractiveMessageReply()) {
//                        $selectedValue = $response->getValue(); // will be either 'yes' or 'no'
//                        $selectedText = $response->getText(); // will be either 'Of course' or 'Hell no!'
//                        var_dump($selectedText);
//                        $this->firstname = $response->getText();
//                    }
                });
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

    public function sendMessageWithUsersHooks()
    {
        $this->botman->hears('hook', function ($bot) {
            $urls = ['https://hooks.slack.com/services/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'];

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
                    'Content-Length: ' . strlen($data_string)));
                //Execute CURL
                $result = curl_exec($ch);
            }

            return $result;
        });
    }
}
