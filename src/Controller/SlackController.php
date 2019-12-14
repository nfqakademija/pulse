<?php

namespace App\Controller;

use Maknz\Slack\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SlackController extends AbstractController
{
    /**
     * @Route(path = "/ask_poll", name = "ask_poll")
     */
    public function triggerTheBot(Request $request)
    {
        $id = $request->query->get('id');

        try {
            $hookUrl = $_ENV['WEB_HOOK'];
            $settings = [
                'username' => 'admin',
                'channel' => '@arvbot',
            ];
            $client = new Client($hookUrl, $settings);
            $msg = 'Send poll form panel ' . $id;
            $client->send($msg);
        } catch (\Throwable $t) {
            var_dump($t);
        }

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => 'Poll',
        ]);
    }
}
