<?php

namespace App\Model;

/**
 * Class CredentialInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface CredentialInterface
{
    /**
     * @return string
     */
    public function getUsername(): string;

    /**
     * @return string
     */
    public function getPassword(): string;
}
