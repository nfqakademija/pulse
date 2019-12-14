<?php

namespace App\Controller;

use Maknz\Slack\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SlackController extends AbstractController
{
    /**
     * @Route(path = "/ask_poll/team", name = "send_team")
     */
    public function triggerTheBotForTeam(Request $request)
    {
        $id = $request->query->get('id');
        $msg = 'team_poll: ' . $id;
        $this->triggerTheBot($msg);

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => 'Poll',
        ]);
    }
    /**
     * @Route(path = "/ask_poll/workspace", name = "send_workspace")
     */
    public function triggerTheBotForWorkspace(Request $request)
    {
        $id = $request->query->get('id');
        $msg = 'workspace_poll: ' . $id;
        $this->triggerTheBot($msg);

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => 'Poll',
        ]);
    }

    protected function triggerTheBot(string $msg)
    {
        try {
            $hookUrl = $_ENV['WEB_HOOK'];
            $settings = [
                'username' => 'admin',
                'channel' => '@arvbot',
            ];
            $client = new Client($hookUrl, $settings);

            $client->send($msg);
        } catch (\Throwable $t) {
            var_dump($t);
        }
    }
}
