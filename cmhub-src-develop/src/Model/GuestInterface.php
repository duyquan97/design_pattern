<?php

namespace App\Model;

/**
 * Interface GuestInterface
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
interface GuestInterface
{
    /**
     *
     * @return bool
     */
    public function isMain();

    /**
     *
     * @return int
     */
    public function getAge();

    /**
     *
     * @return null|string
     */
    public function getName();

    /**
     *
     * @return null|string
     */
    public function getSurname();

    /**
     *
     * @return null|string
     */
    public function getEmail();

    /**
     *
     * @return null|string
     */
    public function getPhone();

    /**
     *
     * @return null|string
     */
    public function getCountry();

    /**
     *
     * @return null|string
     */
    public function getCountryCode();

    /**
     *
     * @return null|string
     */
    public function getAddress();

    /**
     *
     * @return null|string
     */
    public function getCity();

    /**
     *
     * @return null|string
     */
    public function getPostalCode();

    /**
     *
     * @return null|string
     */
    public function getState();

    /**
     *
     * @return BookingProductInterface
     */
    public function getBookingProduct();
}
