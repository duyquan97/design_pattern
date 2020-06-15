<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * Class ChannelManager
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 *
 * @ORM\Entity()
 * @ORM\Table(name="channel_managers")
 */
class ChannelManager
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
     * @ORM\Column(type="string")
     */
    private $identifier;

    /**
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $name;

    /**
     *
     * @var bool
     *
     * @ORM\Column(name="push_bookings", type="boolean")
     */
    private $pushBookings = false;

    /**
     *
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="create")
     *
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $createdAt;

    /**
     *
     * @var \DateTime
     *
     * @Gedmo\Timestampable(on="update")
     *
     * @ORM\Column(type="datetime", options={"default": "CURRENT_TIMESTAMP"})
     */
    private $updatedAt;

    /**
     * @var CmUser
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\CmUser")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $user;

    /**
     *
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     *
     * @return string
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     *
     * @param string $identifier
     *
     * @return ChannelManager
     */
    public function setIdentifier(string $identifier): ChannelManager
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     *
     * @param string $name
     *
     * @return ChannelManager
     */
    public function setName(string $name): ChannelManager
    {
        $this->name = $name;

        return $this;
    }

    /**
     *
     * @return bool
     */
    public function isPushBookings(): bool
    {
        return $this->pushBookings;
    }

    /**
     * @return string
     */
    public function getPushBookingsString(): string
    {
        return $this->pushBookings ? 'Yes' : 'No';
    }

    /**
     *
     * @param bool $pushBookings
     *
     * @return ChannelManager
     */
    public function setPushBookings(bool $pushBookings): ChannelManager
    {
        $this->pushBookings = $pushBookings;

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
     * @return string
     */
    public function getCreatedAtFormatted(): string
    {
        return $this->createdAt->format('Y-m-d H:i:s');
    }

    /**
     * @param \DateTime $createdAt
     *
     * @return ChannelManager
     */
    public function setCreatedAt(\DateTime $createdAt): ChannelManager
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }

    /**
     * @return string
     */
    public function getUpdatedAtFormatted(): string
    {
        return $this->updatedAt->format('Y-m-d H:i:s');
    }

    /**
     *
     * @param \DateTime $updatedAt
     *
     * @return ChannelManager
     */
    public function setUpdatedAt(\DateTime $updatedAt): ChannelManager
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     *
     * @return CmUser
     */
    public function getUser(): ?CmUser
    {
        return $this->user;
    }

    /**
     *
     * @param CmUser $user
     *
     * @return ChannelManager
     */
    public function setUser(?CmUser $user): ChannelManager
    {
        $this->user = $user;

        return $this;
    }

    /**
     *
     * @return bool
     */
    public function hasPartnerLevelAuth(): bool
    {
        if (!$this->getUser()) {
            return true;
        }

        return false;
    }

    /**
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'id'            => $this->getIdentifier(),
            'name'          => $this->getName(),
            'identifier'    => $this->getIdentifier(),
            'push_bookings' => $this->isPushBookings(),
        ];
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getName() ?: 'Channel Manager';
    }
}
