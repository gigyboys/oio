<?php

namespace App\Model;

class SchoolInit
{
    private $name;
    private $shortName;
    private $slogan;
    private $slug;
	private $typeId;
	private $optionId;
	private $shortDescription;
	private $description;

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }


    public function setShortName($shortName)
    {
        $this->shortName = $shortName;

        return $this;
    }

    public function getShortName()
    {
        return $this->shortName;
    }


    public function setSlogan($slogan)
    {
        $this->slogan = $slogan;

        return $this;
    }

    public function getSlogan()
    {
        return $this->slogan;
    }


    public function setSlug($slug)
    {
        $this->slug = $slug;

        return $this;
    }

    public function getSlug()
    {
        return $this->slug;
    }


    public function setTypeId($typeId)
    {
        $this->typeId = $typeId;

        return $this;
    }

    public function getTypeId()
    {
        return $this->typeId;
    }

    public function setOptionId($optionId)
    {
        $this->optionId = $optionId;

        return $this;
    }

    public function getOptionId()
    {
        return $this->optionId;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;
    }

}
