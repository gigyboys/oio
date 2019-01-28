<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table(name="et_tag_event")
 * @ORM\Entity(repositoryClass="App\Repository\TagEventRepository")
 */
class TagEvent
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event")
     * @ORM\JoinColumn(name="post_id", nullable=false)
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tag")
     * @ORM\JoinColumn(name="tag_id", nullable=false)
     */
    private $tag;

    /**
     * @var boolean
     *
     * @ORM\Column(name="current", type="boolean")
     */
    private $current;

    public function getId()
    {
        return $this->id;
    }

    public function setEvent($event)
    {
        $this->event = $event;

        return $this;
    }

    public function getEvent()
    {
        return $this->event;
    }

    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function setCurrent($current)
    {
        $this->current = $current;

        return $this;
    }

    public function getCurrent()
    {
        return $this->current;
    }
}
