<?php

namespace App\Entity;

use App\Application\Sonata\UserBundle\Entity\User;
use App\Model\ImportDataType;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class ChainingFile
 *
 * @author Smartbox Web Team <si-web@smartbox.com>
 *
 * @ORM\Entity(repositoryClass="App\Repository\ImportDataRepository")
 * @ORM\Table(name="import_data_history")
 * @ORM\EntityListeners({"App\Repository\Listener\ImportDataListener"})
 */
class ImportData
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
    private $filename;

    /**
     * @var string
     *
     * @ORM\Column(type="string")
     */
    private $path;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="App\Application\Sonata\UserBundle\Entity\User")
     * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255, nullable=false, options={"default": "Room"})
     *
     * @Assert\Choice(choices = { "Room", "Availability", "Experience" }, message="The value you provided is not valid. 'Room' or 'Availability' or 'Experience' allowed")
     */
    private $type = ImportDataType::CHAINING_ROOM;

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
     * Unmapped property to handle file uploads
     */
    private $file;

    /**
     * @var bool
     *
     * @ORM\Column(type="boolean")
     */
    private $imported = false;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $processedRows = 0;

    /**
     * @var int
     *
     * @ORM\Column(type="integer")
     */
    private $errors = 0;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param UploadedFile $file
     *
     * @return self
     */
    public function setFile(UploadedFile $file = null): ImportData
    {
        $this->file = $file;

        return $this;
    }

    /**
     * @return UploadedFile
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @return string
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * @param string $filename
     *
     * @return self
     */
    public function setFilename(string $filename): ImportData
    {
        $this->filename = $filename;

        return $this;
    }

    /**
     * @return string
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * @param string $path
     *
     * @return self
     */
    public function setPath(string $path): ImportData
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @return User
     */
    public function getAuthor(): ?User
    {
        return $this->author;
    }

    /**
     * @param User $author
     *
     * @return self
     */
    public function setAuthor(?User $author): ImportData
    {
        $this->author = $author;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getType(): ?string
    {
        return $this->type;
    }

    /**
     * @param string $type
     *
     * @return self
     */
    public function setType(string $type): ImportData
    {
        $this->type = $type;

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
     * @return \DateTime
     */
    public function getUpdatedAt(): \DateTime
    {
        return $this->updatedAt;
    }


    /**
     * Manages the copying of the file to the relevant place on the server
     *
     * @param string $uploadFolder
     *
     * @return void
     */
    public function upload(string $uploadFolder): void
    {
        $uploadFolder = rtrim($uploadFolder, '/');
        // the file property can be empty if the field is not required
        if (null === $this->getFile()) {
            return;
        }

        // we use the original file name here but you should
        // sanitize it at least to avoid any security issues
        $newFilename = date('U') . '-' . $this->getFile()->getClientOriginalName();
        // move takes the target directory and target filename as params
        $this->getFile()->move(
            $uploadFolder,
            $newFilename
        );

        // set the path property to the filename where you've saved the file
        $this->setFilename($newFilename);
        $this->setPath(realpath($uploadFolder) . '/' . $newFilename);

        // clean up the file property as you won't need it anymore
        $this->setFile(null);
    }

    /**
     *
     * @return bool
     */
    public function isImported(): bool
    {
        return $this->imported;
    }

    /**
     *
     * @param bool $imported
     *
     * @return ImportData
     */
    public function setImported(bool $imported): ImportData
    {
        $this->imported = $imported;

        return $this;
    }

    /**
     *
     * @return int
     */
    public function getProcessedRows(): int
    {
        return $this->processedRows;
    }

    /**
     *
     * @param int $processedRows
     *
     * @return ImportData
     */
    public function setProcessedRows(int $processedRows): ImportData
    {
        $this->processedRows = $processedRows;

        return $this;
    }

    /**
     *
     * @return ImportData
     */
    public function increaseProcessedRows(): ImportData
    {
        ++$this->processedRows;

        return $this;
    }

    /**
     *
     * @return int
     */
    public function getErrors(): int
    {
        return $this->errors;
    }

    /**
     *
     * @param int $errors
     *
     * @return ImportData
     */
    public function setErrors(int $errors): ImportData
    {
        $this->errors = $errors;

        return $this;
    }

    /**
     *
     * @return ImportData
     */
    public function increaseError(): ImportData
    {
        ++$this->errors;

        return $this;
    }

    /**
     *
     * @return string
     */
    public function __toString()
    {
        return $this->filename;
    }
}
