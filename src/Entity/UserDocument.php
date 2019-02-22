<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="ur_user_document")
 * @ORM\Entity(repositoryClass="App\Repository\UserDocumentRepository")
 * @ORM\HasLifecycleCallbacks()
 */
class UserDocument
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
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @Assert\NotBlank(message="Please, upload a file.")
     * @Assert\File(maxSize="100m", maxSizeMessage = "The file is too large ({{ size }})")
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
        return 'userdocument';
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
