<?php

namespace App\Service\DataImport\Model;

use App\Entity\Experience;

/**
 * Class ExperienceRow
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ExperienceRow implements ImportDataRowInterface
{

    /**
     * @var Experience
     */
    private $experience;

    /**
     *
     * @var \Exception
     */
    private $exception;

    /**
     *
     * @return \Exception
     */
    public function getException(): ?\Exception
    {
        return $this->exception;
    }

    /**
     *
     * @param \Exception $exception
     *
     * @return self
     */
    public function setException(\Exception $exception): ImportDataRowInterface
    {
        $this->exception = $exception;

        return $this;
    }

    /**
     *
     * @return bool
     */
    public function hasException(): bool
    {
        return $this->exception ? true : false;
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->getExperience();
    }

    /**
     * @return Experience
     */
    public function getExperience(): Experience
    {
        return $this->experience;
    }

    /**
     * @param Experience $experience
     *
     * @return self
     */
    public function setExperience(Experience $experience): self
    {
        $this->experience = $experience;

        return $this;
    }
}
