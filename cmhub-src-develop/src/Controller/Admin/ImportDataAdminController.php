<?php

namespace App\Controller\Admin;

use App\Entity\ImportData;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Class ImportDataAdminController
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ImportDataAdminController extends CRUDController
{
    /**
     * @param string $id
     *
     * @return Response
     */
    public function downloadAction(string $id)
    {
        $importData = $this->getDoctrine()->getRepository(ImportData::class)->find($id);
        if (!$importData) {
            throw new NotFoundHttpException('The requested resource does not exist or you don\'t have enough permission');
        }

        $filePath = $importData->getPath() . '.errors';
        if (!file_exists($filePath)) {
            throw new NotFoundHttpException('The requested resource does not exist or you don\'t have enough permission');
        }

        return $this->file($filePath);
    }
}
