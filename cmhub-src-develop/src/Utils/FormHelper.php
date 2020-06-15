<?php

namespace App\Utils;

use Symfony\Component\Form\FormInterface;

/**
 * Class FormHelper
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class FormHelper
{
    /**
     *
     * @param FormInterface $form
     *
     * @return array
     */
    public function getErrorsFromForm(FormInterface $form)
    {
        $errors = array();
        foreach ($form->getErrors() as $error) {
            $errors[] = $error->getMessage();
        }

        foreach ($form->all() as $childForm) {
            if ($childForm instanceof FormInterface && $childErrors = $this->getErrorsFromForm($childForm)) {
                $errors[$childForm->getName()] = $childErrors;
            }
        }

        return $errors;
    }
}
