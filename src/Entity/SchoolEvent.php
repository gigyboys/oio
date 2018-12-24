<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Table(name="pm_school_event")
 * @ORM\Entity(repositoryClass="App\Repository\SchoolEventRepository")
 */
class SchoolEvent
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Event")
     * @ORM\JoinColumn(name="event_id", nullable=false)
     */
    private $event;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\School")
     * @ORM\JoinColumn(name="school_id", nullable=false)
     */
    private $school;

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

    public function setSchool($school)
    {
        $this->school = $school;

        return $this;
    }

    public function getSchool()
    {
        return $this->school;
    }
}
