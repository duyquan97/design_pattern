<?php

namespace App\Message;

/**
 * Class ImportDataFileUploaded
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class ImportDataFileUploaded extends AbstractMessage
{
    /**
     * The ImportData entity ID
     *
     * @var int
     */
    private $id;

    /**
     * ImportDataFileUploaded constructor.
     *
     * @param int $id
     */
    public function __construct(int $id)
    {
        $this->id = $id;
    }

    /**
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}
