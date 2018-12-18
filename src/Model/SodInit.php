<?php
/**
 * Created by PhpStorm.
 * User: Gigy
 * Date: 30/11/2018
 * Time: 15:33
 */

namespace App\Model;


class SodInit
{
    private $datebegin;
    private $dateend;
    private $schoolId;

    public function getDatebegin(){
        return $this->datebegin;
    }

    public function setDatebegin($datebegin){
        $this->datebegin = $datebegin;
    }

    public function getDateend(){
        return $this->dateend;
    }

    public function setDateend($dateend){
        $this->dateend = $dateend;
    }

    public function getSchoolId()
    {
        return $this->schoolId;
    }

    public function setSchoolId($schoolId): void
    {
        $this->schoolId = $schoolId;
    }

}