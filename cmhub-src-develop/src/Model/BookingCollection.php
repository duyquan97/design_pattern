<?php

namespace App\Model;

/**
 * Class BookingCollection
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class BookingCollection implements BookingCollectionInterface, \Iterator
{
    /**
     *
     * @var BookingInterface[]
     */
    private $bookings = [];

    /**
     *
     * @var int
     */
    private $index = 0;

    /**
     *
     * @return BookingInterface[]
     */
    public function getBookings(): array
    {
        return $this->bookings;
    }

    /**
     *
     * @param BookingInterface[] $bookings
     *
     * @return BookingCollection
     */
    public function setBookings(array $bookings): BookingCollection
    {
        $this->bookings = $bookings;

        return $this;
    }

    /**
     *
     * @param BookingInterface $booking
     *
     * @return BookingCollection
     */
    public function addBooking(BookingInterface $booking): BookingCollection
    {
        $this->bookings[] = $booking;

        return $this;
    }

    /**
     *
     * @return Booking
     */
    public function current()
    {
        return $this->bookings[$this->index];
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
        return isset($this->bookings[$this->key()]);
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
