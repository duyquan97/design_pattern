<?php

namespace App\Model;

/**
 * Class AbstractWeekdayCollection
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
abstract class AbstractWeekdayCollection implements WeekdayCollectionInterface
{
    /**
     * @var array
     */
    protected $weekdays;

    /**
     * AbstractWeekdayCollection constructor.
     */
    public function __construct()
    {
        $this->weekdays = [];
    }

    /**
     * @param array $weekdays
     *
     * @return WeekdayCollectionInterface
     */
    public function setEnabledWeekDays(array $weekdays): WeekdayCollectionInterface
    {
        $this->weekdays = $weekdays;

        return $this;
    }

    /**
     * @param \DateTime $date
     *
     * @return bool
     */
    public function isDateInWeekdays(\DateTime $date): bool
    {
        $weekday = $date->format("w");

        return in_array($weekday, $this->weekdays);
    }

    /**
     * @param mixed $weekdaysConfig
     *
     * @return WeekdayCollectionInterface
     */
    public function addEnabledWeekDays($weekdaysConfig): WeekdayCollectionInterface
    {
        foreach ($weekdaysConfig as $key => $value) {
            if (array_key_exists($key, WeekDay::WEEKDAYS) && true === $value) {
                $this->weekdays[] = WeekDay::WEEKDAYS[$key];
            }
        }

        return $this;
    }
}
