<?php

namespace App\Controller;

use App\Entity\Answer;
use App\Entity\Option;
use App\Entity\Poll;
use App\Entity\Question;
use App\Entity\Responder;
use App\Entity\Survey;
use App\Entity\User;
use App\Form\NewPollType;
use Maknz\Slack\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;

class HomeController extends AbstractController
{
    /**
     * @Route("/trigger", name="trigger")
     */
    public function triggerTheBot(KernelInterface $kernelInterface)
    {
        try {
            $hookUrl = $_ENV['WEB_HOOK'];
            $settings = [
                'username' => 'admin',
                'channel' => '@arvbot',
            ];

            $client = new Client($hookUrl, $settings);

            $client->send('send form panel');
        } catch (\Throwable $t) {
            var_dump($t);
        }

        return $this->redirectToRoute('easyadmin', [
            'action' => 'list',
            'entity' => 'Poll',
        ]);
    }

    /**
     * @Route("/", name="home")
     */
    public function index(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $fileImport = array('userImportFile' => '');

        $form = $this->createFormBuilder($fileImport)
            ->add('userImportFile', FileType::class, [
                'label' => 'User Import (CSV)',
                'mapped' => false,
                'attr' => [
                    'class' => 'form-control',
                    'style' => 'margin-bottom: 20px;',
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '1024k',
                        'mimeTypes' => [
                            'text/csv',
                            'text/plain',
                        ],
                        'mimeTypesMessage' => 'Please upload a valid CSV file',
                    ])
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Import',
                'attr' => [
                    'class' => 'btn btn-primary',
                ],
            ])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $userImportFile */
            $userImportFile = $form['userImportFile']->getData();

            $fileExtension = $userImportFile->guessExtension();

            $allowedExtensions = ['csv', 'txt'];

            if (in_array($fileExtension, $allowedExtensions)) {
                // Remove empty lines
                file_put_contents(
                    $userImportFile->getRealPath(),
                    preg_replace(
                        '~[\r\n]+~',
                        "\r\n",
                        trim(file_get_contents($userImportFile->getRealPath()))
                    )
                );

                $file = fopen($userImportFile->getRealPath(), 'r');

                $keys = fgetcsv($file);

                $invalidKey = '';

                $invalidKeyFound = false;

                $noChangesWereMade = true;

                $invalidLinesWereFound = false;

                $addedRespondersCount = 0;

                $updatedRespondersCount = 0;

                while (($line = fgetcsv($file)) !== false) {
                    if (count($keys) > 0 && count($line) > 0 && count($keys) === count($line)) {
                        $i = -1;

                        $responderProperties = array();

                        foreach ($keys as $key) {
                            $i++;
                            $responderProperties[$key] = $line[$i];
                        }

                        $responder = new Responder();

                        $persist = true;

                        $responderDataIsValid = true;

                        foreach ($responderProperties as $key => $value) {
                            switch ($key) {
                                case 'Slack id':
                                    if (!empty($value)) {
                                        $existingResponder = $this->getDoctrine()
                                            ->getRepository(Responder::class)
                                            ->find($value);

                                        if (!empty($existingResponder)) {
                                            $persist = false;
                                        }

                                        $responder->setSlackId($value);
                                    } else {
                                        $responderDataIsValid = false;
                                    }
                                    break;
                                case 'Email':
                                    if (!empty($value)) {
                                        $responder->setEmail($value);
                                    }
                                    break;
                                case 'Slack username':
                                    if (!empty($value)) {
                                        $responder->setSlackUsername($value);
                                    }
                                    break;
                                case 'Department':
                                    if (!empty($value)) {
                                        $responder->setDepartment($value);
                                    }
                                    break;
                                case 'Job title':
                                    if (!empty($value)) {
                                        $responder->setJobTitle($value);
                                    }
                                    break;
                                case 'Reports to':
                                    if (!empty($value)) {
                                        $teamLead = $this->getDoctrine()
                                            ->getRepository(User::class)
                                            ->findOneBy(array('email' => $value));

                                        if (!empty($teamLead)) {
                                            $responder->setTeamLead($teamLead);
                                        }
                                    }
                                    break;
                                case 'Full name':
                                    if (!empty($value)) {
                                        $responder->setFullName($value);
                                    }
                                    break;
                                case 'Site':
                                    if (!empty($value)) {
                                        $responder->setSite($value);
                                    }
                                    break;
                                case 'Team':
                                    if (!empty($value)) {
                                        $responder->setTeam($value);
                                    }
                                    break;
                                default:
                                    $invalidKey = $key;
                                    $invalidKeyFound = true;
                                    break 3;
                            }
                        }

                        if ($responderDataIsValid) {
                            $noChangesWereMade = false;

                            if ($persist) {
                                $addedRespondersCount++;

                                $entityManager->persist($responder);
                            } else {
                                $updatedRespondersCount++;

                                $updatedResponder = $this->getDoctrine()
                                    ->getRepository(Responder::class)
                                    ->find($responder->getSlackId());

                                $updatedResponder->setEmail($responder->getEmail());
                                $updatedResponder->setSlackUsername($responder->getSlackUsername());
                                $updatedResponder->setDepartment($responder->getDepartment());
                                $updatedResponder->setJobTitle($responder->getJobTitle());
                                $updatedResponder->setTeamLead($responder->getTeamLead());
                                $updatedResponder->setFullName($responder->getFullName());
                                $updatedResponder->setSite($responder->getSite());
                                $updatedResponder->setTeam($responder->getTeam());
                            }

                            $entityManager->flush();
                        }
                    } else {
                        $invalidLinesWereFound = true;

                        $this->addFlash(
                            'info',
                            'Invalid line values or value count: "' . implode('","', $line) . '"'
                        );
                    }
                }

                fclose($file);

                if ($invalidKeyFound) {
                    $this->addFlash(
                        'info',
                        'File contains invalid key (' . $invalidKey . ')!'
                    );
                }

                if ($noChangesWereMade) {
                    $this->addFlash(
                        'info',
                        'No changes were made (please check CSV file structure)!'
                    );
                }

                if (!$noChangesWereMade && $invalidLinesWereFound) {
                    $this->addFlash(
                        'info',
                        'Added responders count: ' . $addedRespondersCount
                    );

                    $this->addFlash(
                        'info',
                        'Updated responders count: ' . $updatedRespondersCount
                    );
                }

                if (!$invalidKeyFound && !$noChangesWereMade && !$invalidLinesWereFound) {
                    return $this->redirectToRoute('easyadmin', [
                        'action' => 'list',
                        'entity' => 'Responder',
                    ]);
                }
            } else {
                $this->addFlash(
                    'info',
                    'Invalid file extension!'
                );
            }
        }

        return $this->render('home/index.html.twig', [
            'title' => 'User Import',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/polls/{adminId}", name="polls", methods={"GET", "POST"})
     */
    public function polls(Request $request, KernelInterface $kernelInterface, $adminId)
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
                    'value' => $botSettings['webHook'],
                    'style' => 'margin-bottom: 20px;',
                    'pattern' => '[a-zA-Z0-9_-]+',
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

            return $this->redirectToRoute('polls', ['adminId' => $adminId]);
        }

        $polls = $this->getDoctrine()->getRepository(Poll::class)->findByUser($adminId);

        $surveys = $this->getDoctrine()->getRepository(Survey::class)->findByUser($adminId);

        return $this->render('home/polls.html.twig', [
            'title' => 'Polls',
            'form' => $form->createView(),
            'polls' => $polls,
            'surveys' => $surveys,
        ]);
    }

    /**
     * @Route("/poll/new/{adminId}", name="add_poll", methods={"GET", "POST"})
     */
    public function addPoll($adminId)
    {
        $admin = $this->getDoctrine()->getRepository(User::class)->find($adminId);

        $entityManager = $this->getDoctrine()->getManager();

        $poll = new Poll();
        $poll->setName('New Poll');
        $poll->setUser($admin);

        $entityManager->persist($poll);
        $entityManager->flush();

        return $this->redirectToRoute('show_poll', ['id' => $poll->getId()]);
    }

    /**
     * @Route("/poll/{id}", name="show_poll", methods={"GET", "POST"})
     */
    public function showPoll(Request $request, $id)
    {
        $poll = $this->getDoctrine()->getRepository(Poll::class)->find($id);

        $form = $this->createForm(NewPollType::class, $poll);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            return $this->redirectToRoute('polls', ['adminId' => $poll->getUser()->getId()]);
        }

        return $this->render('home/poll.html.twig', [
            'title' => 'Poll',
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/poll/{pollId}/new/question/{questionId}", name="add_poll_question", methods={"GET", "POST"})
     */
    public function addPollQuestion($pollId, $questionId)
    {
        $poll = $this->getDoctrine()->getRepository(Poll::class)->find($pollId);

        $entityManager = $this->getDoctrine()->getManager();

        $pollQuestions = $this->getDoctrine()->getRepository(Question::class)->findByPoll($pollId);

        $newQuestion = new Question();
        $newQuestion->setPoll($poll);
        $newQuestion->setQuestion('New Question');

        if (empty($pollQuestions)) {
            $newQuestion->setQuestionNumber(1);
        } else {
            $question = $this->getDoctrine()->getRepository(Question::class)->find($questionId);

            $questionNumber = $question->getQuestionNumber();

            foreach ($pollQuestions as $pollQuestion) {
                $pollQuestionNumber = $pollQuestion->getQuestionNumber();

                if ($pollQuestionNumber > $questionNumber) {
                    $pollQuestion->setQuestionNumber($pollQuestionNumber + 1);
                }
            }

            $newQuestion->setQuestionNumber($questionNumber + 1);
        }

        $entityManager->persist($newQuestion);
        $entityManager->flush();

        return $this->redirectToRoute('show_poll', ['id' => $pollId]);
    }

    /**
     * @Route("/question/delete/{questionId}", methods={"POST"})
     */
    public function deletePollQuestion($questionId)
    {
        $question = $this->getDoctrine()->getRepository(Question::class)->find($questionId);

        $pollId = $question->getPoll()->getId();

        $entityManager = $this->getDoctrine()->getManager();

        $pollQuestions = $this->getDoctrine()->getRepository(Question::class)->findByPoll($pollId);

        $questionNumber = $question->getQuestionNumber();

        if ($pollQuestions[array_key_last($pollQuestions)]->getQuestionNumber() !== $questionNumber) {
            foreach ($pollQuestions as $pollQuestion) {
                $pollQuestionNumber = $pollQuestion->getQuestionNumber();

                if ($pollQuestionNumber > $questionNumber) {
                    $pollQuestion->setQuestionNumber($pollQuestionNumber - 1);
                }
            }
        }

        $this->getDoctrine()->getRepository(Option::class)->deleteByQuestion($questionId);

        $entityManager->remove($question);
        $entityManager->flush();

        $response = new Response();
        $response->send();
    }

    /**
     * @Route("/question/{questionId}/new/option", name="add_question_option", methods={"GET", "POST"})
     */
    public function addQuestionOption($questionId)
    {
        $question = $this->getDoctrine()->getRepository(Question::class)->find($questionId);

        $entityManager = $this->getDoctrine()->getManager();

        $option = new Option();
        $option->setQuestion($question);
        $option->setValue('New Option');

        $entityManager->persist($option);
        $entityManager->flush();

        return $this->redirectToRoute('show_poll', ['id' => $question->getPoll()->getId()]);
    }

    /**
     * @Route("/option/delete/{optionId}", methods={"POST"})
     */
    public function deleteQuestionOption($optionId)
    {
        $option = $this->getDoctrine()->getRepository(Option::class)->find($optionId);

        $entityManager = $this->getDoctrine()->getManager();

        $entityManager->remove($option);
        $entityManager->flush();

        $response = new Response();
        $response->send();
    }

    /**
     * @Route("/graph/{surveyId}", name="graph", methods={"GET"})
     */
    public function graph($surveyId)
    {
        $survey = $this->getDoctrine()->getRepository(Survey::class)->find($surveyId);

        $answers = $this->getDoctrine()->getRepository(Answer::class)->findBySurvey($surveyId);

        $assocAnswerArray = array();

        foreach ($answers as $answer) {
            $questionNumber = $answer['question_number'];

            $question = $answer['question'];

            $optionId = $answer['optionId'];

            $optionValue = $answer['value'];

            $optionCount = $answer['count'];

            if (!array_key_exists($questionNumber, $assocAnswerArray)) {
                $assocAnswerArray[$questionNumber] = array();
                $assocAnswerArray[$questionNumber]['question'] = $question;
                $assocAnswerArray[$questionNumber]['options'] = array();
            }

            $assocAnswerArray[$questionNumber]['options'][$optionId] = array();
            $assocAnswerArray[$questionNumber]['options'][$optionId]['value'] = $optionValue;
            $assocAnswerArray[$questionNumber]['options'][$optionId]['count'] = $optionCount;
        }

        return $this->render('home/graph.html.twig', [
            'title' => 'Graph',
            'survey' => $survey,
            'answers' => $assocAnswerArray,
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

            if (
                stristr($line, 'BOT_TOKEN')
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
                if (preg_match('/^[a-zA-Z0-9_-]+$/', $newWebHook)) {
                    $line = 'WEB_HOOK="' . $newWebHook . '"' . "\n";
                } else {
                    $line = 'WEB_HOOK="invalid"' . "\n";
                }

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
