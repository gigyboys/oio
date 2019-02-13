<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Table(name="ur_user")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Post", mappedBy="user")
     */
    private $posts;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Event", mappedBy="user")
     */
    private $events;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Job", mappedBy="user")
     */
    private $jobs;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Evaluation", mappedBy="user")
     */
    private $evaluations;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="array")
     */
    private $roles = [];

    /**
     * @var string
     *
     * @ORM\Column(name="location", type="string", length=255, nullable=true)
     */
    private $location;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(name="last_activity", type="datetime")
     */
    private $lastActivity;

    /**
     * @ORM\Column(name="biography", type="text", nullable=true)
     */
    private $biography;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $token;

    /**
     * @ORM\Column(type="boolean")
     */
    private $enabled;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Subscription", mappedBy="user")
     */
    private $subscription;

    public function __construct()
    {
        $this->roles = array("ROLE_USER");
        $this->subscription = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }

    public function addPost($post)
    {
        $this->posts[] = $post;

        return $this;
    }

    public function removePost($post)
    {
        $this->posts->removeElement($post);
    }

    public function getPosts()
    {
        return $this->posts;
    }

    public function addEvent($event)
    {
        $this->events[] = $event;

        return $this;
    }

    public function removeEvent($event)
    {
        $this->events->removeElement($event);
    }

    public function getEvents()
    {
        return $this->events;
    }

    public function addJob($job)
    {
        $this->jobs[] = $job;

        return $this;
    }

    public function removeJob($job)
    {
        $this->jobs->removeElement($job);
    }

    public function getJobs()
    {
        return $this->jobs;
    }

    public function addEvaluation($evaluation)
    {
        $this->evaluations[] = $evaluation;

        return $this;
    }

    public function removeEvaluation($evaluation)
    {
        $this->evaluations->removeElement($evaluation);
    }

    public function getEvaluations()
    {
        return $this->evaluations;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface

    public function getUsername(): string
    {
        return (string) $this->email;
    }
     */

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        return $roles;
        /*
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';
        return array_unique($roles);
        */
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function isAdmin()
    {
        $roles = $this->getRoles();
        if (in_array('ROLE_ADMIN', $roles) || in_array('ROLE_SUPER_ADMIN', $roles))
        {
            return true;
        }

        return false;
    }

    public function isSuperAdmin()
    {
        $roles = $this->getRoles();
        if (in_array('ROLE_SUPER_ADMIN', $roles))
        {
            return true;
        }

        return false;
    }

    public function setLocation($location)
    {
        $this->location = $location;

        return $this;
    }


    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @see UserInterface
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    public function getDate()
    {
        return $this->date;
    }

    public function setLastActivity($lastActivity)
    {
        $this->lastActivity = $lastActivity;

        return $this;
    }

    public function getLastActivity()
    {
        return $this->lastActivity;
    }

    public function setBiography($biography)
    {
        $this->biography = $biography;

        return $this;
    }

    public function getBiography()
    {
        return $this->biography;
    }

    public function getToken(): ?string
    {
        return $this->token;
    }

    public function setToken(string $token): self
    {
        $this->token = $token;

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function isExpired(): ?bool
    {
        return false;
    }

    /**
     * @return Collection|Subscription[]
     */
    public function getSubscription(): Collection
    {
        return $this->subscription;
    }

    public function addSubscription(Subscription $subscription): self
    {
        if (!$this->subscription->contains($subscription)) {
            $this->subscription[] = $subscription;
            $subscription->setSchool($this);
        }

        return $this;
    }

    public function removeSubscription(Subscription $subscription): self
    {
        if ($this->subscription->contains($subscription)) {
            $this->subscription->removeElement($subscription);
            // set the owning side to null (unless already changed)
            if ($subscription->getSchool() === $this) {
                $subscription->setSchool(null);
            }
        }

        return $this;
    }
}
