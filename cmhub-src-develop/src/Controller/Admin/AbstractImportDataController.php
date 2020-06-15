<?php

namespace App\Controller\Admin;

use App\Entity\ImportData;
use App\Form\ImportDataType;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class AbstractImportDataController
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
abstract class AbstractImportDataController extends CRUDController
{

    /**
     * @return string
     */
    abstract public function getTemplate(): string;

    /**
     * @param Request               $request
     * @param TokenStorageInterface $tokenStorage
     * @param ImportData            $importData
     *
     * @return RedirectResponse|Response
     */
    protected function handleRequest(Request $request, TokenStorageInterface $tokenStorage, ImportData $importData)
    {
        $form = $this->createForm(ImportDataType::class, $importData);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $importData->upload($this->getParameter('kernel.root_dir') . '/../public/uploads');
            $entityManager = $this->getDoctrine()->getManager();
            $importData->setAuthor($tokenStorage->getToken() ? $tokenStorage->getToken()->getUser() : null);
            $entityManager->persist($importData);
            $entityManager->flush();

            return $this->redirect($this->generateUrl('import_data_list', ['id' => $importData->getId()]));
        }

        return $this->renderWithExtraParams(
            $this->getTemplate(),
            [
                'form' => $form->createView(),
            ]
        );
    }
}
