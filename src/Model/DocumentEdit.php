<?php

namespace App\Model;

class DocumentEdit
{
    private $name;
	private $description;
	private $authorizationId;
    private $file;

    public function getAuthorizationId()
    {
        return $this->authorizationId;
    }

    public function setAuthorizationId($authorizationId): void
    {
        $this->authorizationId = $authorizationId;
    }

    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    public function getName()
    {
        return $this->name;
    }


    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file)
    {
        $this->file = $file;
    }
}
