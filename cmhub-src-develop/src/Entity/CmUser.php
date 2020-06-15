<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class CmUser
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 *
 * @ORM\Entity(repositoryClass="App\Repository\CmUserRepository")
 * @ORM\Table(name="cm_users")
 */
class CmUser implements UserInterface
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
     * @ORM\Column(type="string", nullable=false, unique=true)
     */
    private $username;

    /**
     *
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     *
     * @var ChannelManager
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\ChannelManager")
     */
    private $channelManager;

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
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     *
     * @param string $username
     *
     * @return CmUser
     */
    public function setUsername(string $username): CmUser
    {
        $this->username = $username;

        return $this;
    }

    /**
     *
     * @param string $password
     *
     * @return CmUser
     */
    public function setPassword(string $password): CmUser
    {
        $this->password = $password;

        return $this;
    }

    /**
     *
     * @return ChannelManager
     */
    public function getChannelManager(): ?ChannelManager
    {
        return $this->channelManager;
    }

    /**
     * @param ChannelManager|null $channelManager
     *
     * @return CmUser
     */
    public function setChannelManager(?ChannelManager $channelManager): CmUser
    {
        $this->channelManager = $channelManager;

        return $this;
    }

    /**
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
     *
     * @param \DateTime $createdAt
     *
     * @return CmUser
     */
    public function setCreatedAt(\DateTime $createdAt): CmUser
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
     * @return CmUser
     */
    public function setUpdatedAt(\DateTime $updatedAt): CmUser
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     *
     * @return mixed
     */
    public function getRoles()
    {
        return [];
    }

    /**
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     *
     * @return null|string
     */
    public function getSalt()
    {
        return null;
    }

    /**
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     *
     * @return mixed
     */
    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'username' => $this->getUsername(),
            'cm'       => ($this->getChannelManager()) ? $this->getChannelManager()->getIdentifier() : '',
        ];
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return $this->username ?: 'Empty username';
    }
}
