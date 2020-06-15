<?php

namespace App\Model;

/**
 * Class WeekdayCollectionInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface WeekdayCollectionInterface
{
    /**
     * @param array $weekDays
     *
     * @return $this
     */
    public function setEnabledWeekDays(array $weekDays): self;

    /**
     * @param mixed $weekdaysConfig
     *
     * @return $this
     */
    public function addEnabledWeekDays($weekdaysConfig): self;
}
