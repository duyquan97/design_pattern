<?php

namespace App\Model;

/**
 * Class AbstractCollection
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
abstract class AbstractCollection implements \Iterator
{
    /**
     *
     * @var array
     */
    public $elements;

    /**
     *
     * @var int
     */
    public $index;

    /**
     *
     * @return ProductRateCollectionInterface
     */
    public function current()
    {
        return $this->elements[$this->index];
    }

    /**
     *
     * @return void
     */
    public function next()
    {
        $this->index++;
    }

    /**
     *
     * @return int
     */
    public function key()
    {
        return $this->index;
    }

    /**
     *
     * @return bool
     */
    public function valid()
    {
        return isset($this->elements[$this->key()]);
    }

    /**
     *
     * @return void
     */
    public function rewind()
    {
        $this->index = 0;
    }
}
