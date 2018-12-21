<?php

namespace App\Model;

class Accessibility
{
	private $categoriesIndex;
	private $schoolsByPage;
	private $postsByPage;
	private $eventsByPage;
	
	
	//categoriesIndex
	public function setCategoriesIndex($categoriesIndex)
    {
        $this->categoriesIndex = $categoriesIndex;

        return $this;
    }

    public function getCategoriesIndex()
    {
        return $this->categoriesIndex;
    }
	
	//schoolsByPage
	public function setSchoolsByPage($schoolsByPage)
    {
        $this->schoolsByPage = $schoolsByPage;

        return $this;
    }

    public function getSchoolsByPage()
    {
        return $this->schoolsByPage;
    }
	
	//postsByPage
	public function setPostsByPage($postsByPage)
    {
        $this->postsByPage = $postsByPage;

        return $this;
    }

    public function getPostsByPage()
    {
        return $this->postsByPage;
    }
	
	//eventsByPage
	public function setEventsByPage($eventsByPage)
    {
        $this->eventsByPage = $eventsByPage;

        return $this;
    }

    public function getEventsByPage()
    {
        return $this->eventsByPage;
    }
}
