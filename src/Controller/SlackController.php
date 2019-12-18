<?php

namespace App\Controller;

use Maknz\Slack\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class SlackController extends AbstractController
{
    /**
     * @Route(path = "/ask_poll/team", name = "send_team")
     * @param Request $request
     * @param SurveyController $s
     * @return RedirectResponse
     */
    public function triggerTheBotForTeam(Request $request, SurveyController $s)
    {
        $id = $request->query->get('id');
        $surveyId = $s->addSurvey($id, "team");
        $msg = 'team_survey: ' . $surveyId;
        $this->triggerTheBot($msg);
        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => 'Poll',
        ]);
    }

    /**
     * @Route(path = "/ask_poll/workspace", name = "send_workspace")
     * @param Request $request
     * @param SurveyController $s
     * @return RedirectResponse
     */
    public function triggerTheBotForWorkspace(Request $request, SurveyController $s)
    {
        $id = $request->query->get('id');
        $surveyId = $s->addSurvey($id, "workspace");
        $msg = 'workspace_survey: ' . $surveyId;
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

    /**
     * @Route("/superadmin/bot/settings", name="bot_settings", methods={"GET", "POST"})
     * @param Request $request
     * @param KernelInterface $kernelInterface
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function botSettings(Request $request, KernelInterface $kernelInterface)
    {
        $botSettings = $this->getBotSettingsFromEnv($kernelInterface);

        $form = $this->createFormBuilder($botSettings)
            ->add('token', TextType::class, [
                'label' => 'Bot Token',
                'attr' => [
                    'class' => 'form-control',
                    'value' => $botSettings['token'],
                    'style' => 'margin-bottom: 20px;',
                    'pattern' => '[a-zA-Z0-9_-]+',
                ],
            ])
            ->add('signingSecret', TextType::class, [
                'label' => 'Slack Signing Secret',
                'attr' => [
                    'class' => 'form-control',
                    'value' => $botSettings['signingSecret'],
                    'style' => 'margin-bottom: 20px;',
                    'pattern' => '[a-zA-Z0-9_-]+',
                ],
            ])
            ->add('webHook', TextType::class, [
                'label' => 'Web hook',
                'attr' => [
                    'class' => 'form-control',
                    'value' => urldecode($botSettings['webHook']),
                    'style' => 'margin-bottom: 20px;',
                ],
            ])
            ->add('workspaceUrl', TextType::class, [
                'label' => 'Workspace URL',
                'attr' => [
                    'class' => 'form-control',
                    'value' => urldecode($botSettings['workspaceUrl']),
                    'style' => 'margin-bottom: 20px;',
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Save',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newToken = $form["token"]->getData();

            $newSigningSecret = $form["signingSecret"]->getData();

            $newWebHook = $form["webHook"]->getData();

            $newWorkspaceUrl = $form["workspaceUrl"]->getData();
            $this->setBotSettingsInEnv($kernelInterface, $newToken, $newSigningSecret, $newWebHook, $newWorkspaceUrl);

            return $this->redirectToRoute('easyadmin');
        }
        return $this->render('bot/settings.html.twig', [
            'title' => 'Bot Settings',
            'form' => $form->createView(),
        ]);
    }

    private function getBotSettingsFromEnv(KernelInterface $kernelInterface): array
    {
        $projectDir = $kernelInterface->getProjectDir();

        $envFile = $projectDir . '/.env.local';

        $botSettings = array('token' => '', 'signingSecret' => '', 'webHook' => '', 'workspaceUrl' => '');

        $reading = fopen($envFile, 'r');

        while (!feof($reading)) {
            $line = fgets($reading);

            if (stristr($line, 'BOT_TOKEN')
                || stristr($line, 'SLACK_SIGNING_SECRET')
                || stristr($line, 'WEB_HOOK')
                || stristr($line, 'WORKSPACE_URL')
            ) {
                $lineChars = str_split($line);

                for ($i = 0; $i < count($lineChars); $i++) {
                    if ($lineChars[$i] === '"') {
                        while ($i + 1 < count($lineChars)) {
                            $i++;

                            if ($lineChars[$i] !== '"') {
                                if (stristr($line, 'BOT_TOKEN')) {
                                    $botSettings['token'] .= $lineChars[$i];
                                } elseif (stristr($line, 'SLACK_SIGNING_SECRET')) {
                                    $botSettings['signingSecret'] .= $lineChars[$i];
                                } elseif (stristr($line, 'WEB_HOOK')) {
                                    $botSettings['webHook'] .= $lineChars[$i];
                                } else {
                                    $botSettings['workspaceUrl'] .= $lineChars[$i];
                                }
                            } else {
                                break;
                            }
                        }
                        break;
                    }
                }
            }
        }

        fclose($reading);

        return $botSettings;
    }

    private function setBotSettingsInEnv(
        KernelInterface $kernelInterface,
        string $newToken,
        string $newSigningSecret,
        string $newWebHook,
        string $newWorkspaceUrl
    )
    {
        $projectDir = $kernelInterface->getProjectDir();

        $envFile = $projectDir . '/.env.local';

        $envTmpFile = $projectDir . '/env.tmp';

        $reading = fopen($envFile, 'r');

        $writing = fopen($envTmpFile, 'w');

        $replaced = false;

        while (!feof($reading)) {
            $line = fgets($reading);

            if (stristr($line, 'BOT_TOKEN')) {
                if (preg_match('/^[a-zA-Z0-9_-]+$/', $newToken)) {
                    $line = 'BOT_TOKEN="' . $newToken . '"' . "\n";
                } else {
                    $line = 'BOT_TOKEN="invalid"' . "\n";
                }

                $replaced = true;
            } elseif (stristr($line, 'SLACK_SIGNING_SECRET')) {
                if (preg_match('/^[a-zA-Z0-9_-]+$/', $newSigningSecret)) {
                    $line = 'SLACK_SIGNING_SECRET="' . $newSigningSecret . '"' . "\n";
                } else {
                    $line = 'SLACK_SIGNING_SECRET="invalid"' . "\n";
                }

                $replaced = true;
            } elseif (stristr($line, 'WEB_HOOK')) {
                $line = 'WEB_HOOK="' . urlencode($newWebHook) . '"' . "\n";

                $replaced = true;
            } elseif (stristr($line, 'WORKSPACE_URL')) {
                $line = 'WORKSPACE_URL="' . urlencode($newWorkspaceUrl) . '"' . "\n";
                $replaced = true;
            }


            fputs($writing, $line);
        }

        fclose($reading);
        fclose($writing);

        if ($replaced) {
            rename($envTmpFile, $envFile);
        } else {
            unlink($envTmpFile);
        }
    }
}
