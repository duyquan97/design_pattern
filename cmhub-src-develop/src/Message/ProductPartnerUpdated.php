<?php

namespace App\Message;

/**
 * Class ProductPartnerUpdated
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ProductPartnerUpdated extends AbstractMessage
{
    /**
     * The Product identifier
     *
     * @var string
     */
    private $identifier;

    /**
     * ProductPartnerUpdated constructor.
     *
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     *
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
