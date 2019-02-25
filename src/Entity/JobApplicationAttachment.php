<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="jb_job_application_attachment")
 * @ORM\Entity(repositoryClass="App\Repository\JobApplicationAttachmentRepository")
 */
class JobApplicationAttachment
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\JobApplication")
     * @ORM\JoinColumn(nullable=false)
     */
    private $jobApplication;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CV")
     */
    private $CV;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\CoverLetter")
     */
    private $coverLetter;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\UserDocument")
     */
    private $userDocument;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getJobApplication(): ?JobApplication
    {
        return $this->jobApplication;
    }

    public function setJobApplication(?JobApplication $jobApplication): self
    {
        $this->jobApplication = $jobApplication;

        return $this;
    }

    public function getCV(): ?CV
    {
        return $this->CVcv;
    }

    public function setCv(?CV $CV): self
    {
        $this->CV = $CV;

        return $this;
    }

    public function getCoverLetter(): ?CoverLetter
    {
        return $this->coverLetter;
    }

    public function setCoverLetter(?CoverLetter $coverLetter): self
    {
        $this->coverLetter = $coverLetter;

        return $this;
    }

    public function getUserDocument(): ?UserDocument
    {
        return $this->userDocument;
    }

    public function setUserDocument(?UserDocument $userDocument): self
    {
        $this->userDocument = $userDocument;

        return $this;
    }
}
