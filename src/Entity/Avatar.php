<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="ur_avatar")
 * @ORM\Entity(repositoryClass="App\Repository\AvatarRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Avatar
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $path;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $originalname;

    /**
     * @ORM\Column(type="boolean")
     */
    private $current;

    /**
     * @Assert\NotBlank(message="Please, upload the product brochure as a PDF file.")
     * @Assert\File(maxSize="5m", maxSizeMessage = "The file is too large ({{ size }})", mimeTypes = {"image/jpg", "image/jpeg", "image/gif", "image/png"}, mimeTypesMessage = "Please upload a valid Image")
     */
    public $file;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    public function getUser()
    {
        return $this->user;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getOriginalname(): ?string
    {
        return $this->originalname;
    }

    public function setOriginalname(?string $originalname): self
    {
        $this->originalname = $originalname;

        return $this;
    }

    public function getCurrent(): ?bool
    {
        return $this->current;
    }

    public function setCurrent(bool $current): self
    {
        $this->current = $current;

        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }


    public function getWebPath()
    {
        return $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadDir()
    {
        return 'uploads/images/user/avatar';
    }

    public function getAbsolutePath()
    {
        return $this->getUploadRootDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        return __DIR__.'/../../public/'.$this->getUploadDir();
    }

    /**
     * @ORM\PostRemove()
     */
    public function removeUpload()
    {
        //deleting main File
        $file = $this->getAbsolutePath();
        if (file_exists($file)) {
            unlink($file);
        }

        //deleting files created by liip
        $dir = "__DIR__.'/../../public/media/";
        if (is_dir($dir)) {
            if ($dh = opendir($dir)) {
                while (($directory = readdir($dh)) !== false) {
                    if (is_dir($dir . $directory) && $directory != '.' && $directory != '..' ) {
                        $file = __DIR__.'/../../public/media/'.$directory.'/'.$this->getUploadDir().'/'.$this->path;
                        if (file_exists($file)) {
                            unlink($file);
                        }
                    }
                }
                closedir($dh);
            }
        }
    }
}
