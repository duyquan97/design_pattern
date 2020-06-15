<?php

namespace App\Message\Factory;

use App\Message\ImportDataFileUploaded;

/**
 * Class ImportDataFileUploadedFactory
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ImportDataFileUploadedFactory
{
    /**
     *
     * @param int $id
     *
     * @return ImportDataFileUploaded
     */
    public function create(int $id)
    {
        return new ImportDataFileUploaded($id);
    }
}
