<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="jb_job")
 * @ORM\Entity(repositoryClass="App\Repository\JobRepository")
 */
class Job
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\View", mappedBy="job")
     */
    private $views;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="jobs")
     * @ORM\JoinColumn(name="user_id", nullable=false)
     */
    private $user;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $title;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $datelimit;

    private $datelimitText;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @var boolean
     *
     * @ORM\Column(name="published", type="boolean")
     */
    private $published;

    /**
     * @var boolean
     *
     * @ORM\Column(name="show_author", type="boolean", options={"default"=1})
     */
    private $showAuthor;

    /**
     * @var boolean
     *
     * @ORM\Column(name="active_comment", type="boolean", options={"default"=1})
     */
    private $activeComment;

    /**
     * @var boolean
     *
     * @ORM\Column(name="tovalid", type="boolean", options={"default"=0})
     */
    private $tovalid;

    /**
     * @var boolean
     *
     * @ORM\Column(name="valid", type="boolean", options={"default"=0})
     */
    private $valid;

    /**
     * @var boolean
     *
     * @ORM\Column(name="deleted", type="boolean", options={"default"=0})
     */
    private $deleted;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $location;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $latitude;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $longitude;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $city;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $society;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $salary;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $phone;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $website;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Sector")
     */
    private $sector;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Contract")
     */
    private $contract;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function addView($view)
    {
        $this->views[] = $view;

        return $this;
    }

    public function removeView($view)
    {
        $this->views->removeElement($view);
    }

    public function getViews()
    {
        return $this->views;
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

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): ?string
    {
        return $this->slug;
    }

    public function setSlug(string $slug): self
    {
        $this->slug = $slug;

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

    public function getDatelimit(): ?\DateTimeInterface
    {
        return $this->datelimit;
    }

    public function setDatelimit($datelimit)
    {
        $this->datelimit = $datelimit;

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

    public function setPublished($published)
    {
        $this->published = $published;

        return $this;
    }

    public function getPublished()
    {
        return $this->published;
    }

    public function setShowAuthor($showAuthor)
    {
        $this->showAuthor = $showAuthor;

        return $this;
    }

    public function getShowAuthor()
    {
        return $this->showAuthor;
    }

    public function setActiveComment($activeComment)
    {
        $this->activeComment = $activeComment;

        return $this;
    }

    public function getActiveComment()
    {
        return $this->activeComment;
    }

    public function setValid($valid)
    {
        $this->valid = $valid;

        return $this;
    }

    public function getValid()
    {
        return $this->valid;
    }

    public function setTovalid($tovalid)
    {
        $this->tovalid = $tovalid;

        return $this;
    }

    public function getTovalid()
    {
        return $this->tovalid;
    }

    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getDeleted()
    {
        return $this->deleted;
    }


    public function getDatelimitText()
    {
        return $this->datelimitText;
    }

    public function setDatelimitText($datelimitText): void
    {
        $this->datelimitText = $datelimitText;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(?string $location): self
    {
        $this->location = $location;

        return $this;
    }

    public function getLatitude(): ?string
    {
        return $this->latitude;
    }

    public function setLatitude(?string $latitude): self
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?string
    {
        return $this->longitude;
    }

    public function setLongitude(?string $longitude): self
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(?string $city): self
    {
        $this->city = $city;

        return $this;
    }

    public function getSociety(): ?string
    {
        return $this->society;
    }

    public function setSociety(?string $society): self
    {
        $this->society = $society;

        return $this;
    }

    public function getSalary(): ?string
    {
        return $this->salary;
    }

    public function setSalary(?string $salary): self
    {
        $this->salary = $salary;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): self
    {
        $this->website = $website;

        return $this;
    }

    public function getSector(): ?Sector
    {
        return $this->sector;
    }

    public function setSector(?Sector $sector): self
    {
        $this->sector = $sector;

        return $this;
    }

    public function getContract(): ?Contract
    {
        return $this->contract;
    }

    public function setContract(?Contract $contract): self
    {
        $this->contract = $contract;

        return $this;
    }
}
