<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Class RequestLog
 *
 * @ORM\Entity()
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 */
class RequestLog
{
    /**
     *
     * @var int
     *
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     *
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $request;

    /**
     *
     * @var string
     *
     * @ORM\Column(type="text")
     */
    private $response;

    /**
     *
     * @var \DateTime
     *
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * RequestLog constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     *
     * @return string|null
     */
    public function getRequest(): ?string
    {
        return $this->request;
    }

    /**
     *
     * @param string $request
     *
     * @return RequestLog
     */
    public function setRequest(string $request): RequestLog
    {
        $this->request = $request;

        return $this;
    }

    /**
     *
     * @return string|null
     */
    public function getResponse(): ?string
    {
        return $this->response;
    }

    /**
     *
     * @param string $response
     *
     * @return RequestLog
     */
    public function setResponse(string $response): RequestLog
    {
        $this->response = $response;

        return $this;
    }

    /**
     *
     * @return \DateTime
     */
    public function getCreatedAt(): \DateTime
    {
        return $this->createdAt;
    }

    /**
     *
     * @param \DateTime $createdAt
     *
     * @return RequestLog
     */
    public function setCreatedAt(\DateTime $createdAt): RequestLog
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
