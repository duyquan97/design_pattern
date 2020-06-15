<?php

namespace App\Controller\Admin;

use App\Entity\ImportData;
use App\Model\ImportDataType as ImportDataFileType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ProductAdminController
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductAdminController extends AbstractImportDataController
{
    public const PRODUCT_IMPORT_FORM_TEMPLATE = 'ProductAdmin/import_form.html.twig';

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
        $importData->setType(ImportDataFileType::CHAINING_ROOM);

        return $this->handleRequest($request, $tokenStorage, $importData);
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return self::PRODUCT_IMPORT_FORM_TEMPLATE;
    }
}
