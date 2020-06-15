<?php

namespace App\Controller\Admin;

use App\Entity\ImportData;
use App\Model\ImportDataType as ImportDataFileType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class AvailabilityAdminController
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class AvailabilityAdminController extends AbstractImportDataController
{
    public const AVAILABILITY_IMPORT_FORM_TEMPLATE = 'AvailabilityAdmin/import_form.html.twig';

    /**
     *
     * @param Request               $request
     * @param TokenStorageInterface $tokenStorage
     *
     * @return RedirectResponse|Response
     */
    public function importAction(Request $request, TokenStorageInterface $tokenStorage)
    {
        $importData = new ImportData();
        $importData->setType(ImportDataFileType::AVAILABILITY);

        return $this->handleRequest($request, $tokenStorage, $importData);
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return self::AVAILABILITY_IMPORT_FORM_TEMPLATE;
    }
}
