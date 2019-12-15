<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

class BotSettingsController extends AbstractController
{
    /**
     * @Route("/bot/settings", name="bot_settings", methods={"GET", "POST"})
     */
    public function botSettings(Request $request, KernelInterface $kernelInterface)
    {
        $botSettings = $this->getBotSettingsFromEnv($kernelInterface);

        $form = $this->createFormBuilder($botSettings)
            ->add('token', TextType::class, [
                'label' => 'BOT_TOKEN',
                'attr' => [
                    'class' => 'form-control',
                    'value' => $botSettings['token'],
                    'style' => 'margin-bottom: 20px;',
                    'pattern' => '[a-zA-Z0-9_-]+',
                ],
            ])
            ->add('signingSecret', TextType::class, [
                'label' => 'SLACK_SIGNING_SECRET',
                'attr' => [
                    'class' => 'form-control',
                    'value' => $botSettings['signingSecret'],
                    'style' => 'margin-bottom: 20px;',
                    'pattern' => '[a-zA-Z0-9_-]+',
                ],
            ])
            ->add('webHook', TextType::class, [
                'label' => 'WEB_HOOK',
                'attr' => [
                    'class' => 'form-control',
                    'value' => urldecode($botSettings['webHook']),
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

            $this->setBotSettingsInEnv($kernelInterface, $newToken, $newSigningSecret, $newWebHook);

            return $this->redirectToRoute('bot_settings');
        }

        return $this->render('bot/settings.html.twig', [
            'title' => 'Bot Settings',
            'form' => $form->createView(),
        ]);
    }

    private function getBotSettingsFromEnv(KernelInterface $kernelInterface): array
    {
        $projectDir = $kernelInterface->getProjectDir();

        $envFile = $projectDir . '/.env';

        $botSettings = array('token' => '', 'signingSecret' => '', 'webHook' => '');

        $reading = fopen($envFile, 'r');

        while (!feof($reading)) {
            $line = fgets($reading);

            if (stristr($line, 'BOT_TOKEN')
                || stristr($line, 'SLACK_SIGNING_SECRET')
                || stristr($line, 'WEB_HOOK')
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
                                } else {
                                    $botSettings['webHook'] .= $lineChars[$i];
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
        string $newWebHook
    ) {
        $projectDir = $kernelInterface->getProjectDir();

        $envFile = $projectDir . '/.env';

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
