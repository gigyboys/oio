<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="sl_cover")
 * @ORM\Entity(repositoryClass="App\Repository\CoverRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Cover
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\School")
     * @ORM\JoinColumn(nullable=false)
     */
    private $school;

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
     * @Assert\File(maxSize="2m", maxSizeMessage = "The file is too large ({{ size }})", mimeTypes = {"image/jpg", "image/jpeg", "image/gif", "image/png"}, mimeTypesMessage = "Please upload a valid Image")
     */
    public $file;

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setSchool($school)
    {
        $this->school = $school;

        return $this;
    }

    public function getSchool()
    {
        return $this->school;
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


    public function getWebPath()
    {
        return $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadDir()
    {
        return 'uploads/images/school/cover';
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
        //Suppression du fichier principal
        $file = $this->getAbsolutePath();
        if (file_exists($file)) {
            unlink($file);
        }

        //Suppression des fichiers dans le dossier créé par le bundle liip . see app/config/liip.yml
        $dossiers = array(
            "20x20",
            "22x22",
            "32x32",
            "36x36",
            "40x40",
            "50x50",
            "60x60",
            "80x80",
            "100x100",
            "116x116",
            "140x140",
            "160x160",
            "170x170",
            "187x123",
            "218x140",
            "228x152",
            "248x165",
            "258x172",
            "263x175",
            "300x100",
            "765x510",
            "960x240",
            "960x300",
            "1200x300"
        );
        foreach ($dossiers as $dossier) {
            $file = __DIR__.'/../../public/media/'.$dossier.'/'.$this->getUploadDir().'/'.$this->path;
            if (file_exists($file)) {
                unlink($file);
            }
        }
    }
}
