<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="bg_post")
 * @ORM\Entity(repositoryClass="App\Repository\PostRepository")
 */
class Post
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\View", mappedBy="post")
     */
    private $views;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="posts")
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
}
