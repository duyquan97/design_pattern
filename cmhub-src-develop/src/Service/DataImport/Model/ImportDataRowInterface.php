<?php

namespace App\Service\DataImport\Model;

/**
 * Class ImportDataRowInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface ImportDataRowInterface
{
    /**
     *
     * @return \Exception
     */
    public function getException(): ?\Exception;

    /**
     *
     * @param \Exception $exception
     *
     * @return self
     */
    public function setException(\Exception $exception): self;

    /**
     *
     * @return bool
     */
    public function hasException(): bool;

    /**
     * @return mixed
     */
    public function getEntity();
}
