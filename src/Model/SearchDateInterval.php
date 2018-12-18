<?php
/**
 * Created by PhpStorm.
 * User: Gigy
 * Date: 30/11/2018
 * Time: 15:33
 */

namespace App\Model;


class SearchDateInterval
{
    private $datebegin;
    private $dateend;

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
}