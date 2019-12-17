<?php

namespace App\Controller;

use App\Entity\Responder;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\File;

class ResponderController extends AbstractController
{
    /**
     * @Route("/superadmin/responder/import", name="responder_import")
     */
    public function importResponders(Request $request)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $fileImport = array('userImportFile' => '');

        $form = $this->createFormBuilder($fileImport)
            ->add('userImportFile', FileType::class, [
                'label' => 'Responder Import (CSV)',
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

        return $this->render('responder/import.html.twig', [
            'title' => 'Responder Import',
            'form' => $form->createView(),
        ]);
    }
}
