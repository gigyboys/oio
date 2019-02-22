<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="sl_document")
 * @ORM\Entity(repositoryClass="App\Repository\DocumentRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class Document
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Download", mappedBy="document")
     */
    private $downloads;

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
    private $published;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @Assert\NotBlank(message="Please, upload a file.")
     * @Assert\File(maxSize="100m", maxSizeMessage = "The file is too large ({{ size }})")
     */
    public $file;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\DocumentAuthorization")
     * @ORM\JoinColumn(nullable=false)
     */
    private $documentAuthorization;

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

    public function getDownloads()
    {
        return $this->downloads;
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

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getOriginalname(): ?string
    {
        return $this->originalname;
    }

    public function setOriginalname(string $originalname): self
    {
        $this->originalname = $originalname;

        return $this;
    }

    public function getPublished(): ?bool
    {
        return $this->published;
    }

    public function setPublished(bool $published): self
    {
        $this->published = $published;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function  getNewName(){
        $originalName = $this->getOriginalName();
        $position = strripos($originalName,".");
        $extension = substr($originalName,$position+1,strlen($originalName));

        if(trim($this->getName()) != ""){
            $newName = $this->getName().".".$extension;
            return $newName;
        }else{
            return $this->getOriginalName();
        }
    }

    public function getWebPath()
    {
        return $this->getUploadDir().'/'.$this->path;
    }

    protected function getUploadDir()
    {
        return 'document';
    }

    public function getAbsolutePath()
    {
        return $this->getUploadRootDir().'/'.$this->path;
    }

    protected function getUploadRootDir()
    {
        return __DIR__.'/../../uploads/'.$this->getUploadDir();
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
    }

    public function getDocumentAuthorization(): ?DocumentAuthorization
    {
        return $this->documentAuthorization;
    }

    public function setDocumentAuthorization(?DocumentAuthorization $documentAuthorization): self
    {
        $this->documentAuthorization = $documentAuthorization;

        return $this;
    }

    public function getFilesize(){
        $size = filesize($this->getAbsolutePath()) ;
        return $size;
    }

    public function getFormattedFilesize(){
        $size = $this->getFilesize() ;
        $formattedSize = $size." Bytes";
        if($size >= 1024 && $size < 1024*1024){
            $sizeTemp = $size / 1024;
            $sizeTemp = number_format($sizeTemp, 2, '.', '');
            $formattedSize = $sizeTemp." KB";
        }elseif($size >= 1024*1024 && $size < 1024*1024*1024){
            $sizeTemp = $size / 1024 / 1024;
            $sizeTemp = number_format($sizeTemp, 2, '.', '');
            $formattedSize = $sizeTemp." MB";
        }elseif($size >= 1024*1024*1024){
            $sizeTemp = $size / 1024 / 1024 / 1024;
            $sizeTemp = number_format($sizeTemp, 2, '.', '');
            $formattedSize = $sizeTemp." GB";
        }
        return $formattedSize;
    }
}
