<?php

namespace App\Model;

class UserPassword
{
	private $currentPassword;
	private $newPassword;
	private $repeatPassword;
	
	//currentPassword
	public function setCurrentPassword($password)
    {
        $this->currentPassword = $password;

        return $this;
    }


    public function getCurrentPassword()
    {
        return $this->currentPassword;
    }
	
	//newPassword
	public function setNewPassword($password)
    {
        $this->newPassword = $password;

        return $this;
    }


    public function getNewPassword()
    {
        return $this->newPassword;
    }
	
	//repeatPassword
	public function setRepeatPassword($password)
    {
        $this->repeatPassword = $password;

        return $this;
    }


    public function getRepeatPassword()
    {
        return $this->repeatPassword;
    }
}
