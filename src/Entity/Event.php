<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="et_event")
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 */
class Event
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\View", mappedBy="event")
     */
    private $views;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="events")
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
     * @ORM\Column(type="datetime")
     */
    private $datebegin;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateend;

    private $datebeginText;
    private $dateendText;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $introduction;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $content;

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

    public function getDatebegin(): ?\DateTimeInterface
    {
        return $this->datebegin;
    }

    public function setDatebegin(\DateTimeInterface $datebegin): self
    {
        $this->datebegin = $datebegin;

        return $this;
    }

    public function getDateend(): ?\DateTimeInterface
    {
        return $this->dateend;
    }

    public function setDateend(\DateTimeInterface $dateend): self
    {
        $this->dateend = $dateend;

        return $this;
    }

    public function getIntroduction(): ?string
    {
        return $this->introduction;
    }

    public function setIntroduction(?string $introduction): self
    {
        $this->introduction = $introduction;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): self
    {
        $this->content = $content;

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


    public function getDatebeginText()
    {
        return $this->datebeginText;
    }

    public function setDatebeginText($datebeginText): void
    {
        $this->datebeginText = $datebeginText;
    }

    public function getDateendText()
    {
        return $this->dateendText;
    }

    public function setDateendText($dateendText): void
    {
        $this->dateendText = $dateendText;
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
}
