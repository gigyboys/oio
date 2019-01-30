<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 *
 * @ORM\Table(name="et_tag_event", 
 *    uniqueConstraints={
 *        @UniqueConstraint(name="tag_event_unique", 
 *            columns={"tag_id", "event_id"})
 *    }
 * )
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
     * @ORM\ManyToOne(targetEntity="App\Entity\Tag")
     * @ORM\JoinColumn(name="tag_id", nullable=false)
     */
    private $tag;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event")
     * @ORM\JoinColumn(name="event_id", nullable=false)
     */
    private $event;

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

    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    public function getTag()
    {
        return $this->tag;
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
