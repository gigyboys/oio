<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 *
 * @ORM\Table(name="sl_category_school", 
 *    uniqueConstraints={
 *        @UniqueConstraint(name="category_school_unique", 
 *            columns={"category_id", "school_id"})
 *    }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\CategorySchoolRepository")
 */
class CategorySchool
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Category")
     * @ORM\JoinColumn(name="category_id", nullable=false)
     */
    private $category;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\School")
     * @ORM\JoinColumn(name="school_id", nullable=false)
     */
    private $school;

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

    public function setCategory($category)
    {
        $this->category = $category;

        return $this;
    }

    public function getCategory()
    {
        return $this->category;
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
