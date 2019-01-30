<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\UniqueConstraint;

/**
 *
 * @ORM\Table(name="bg_tag_post", 
 *    uniqueConstraints={
 *        @UniqueConstraint(name="tag_post_unique", 
 *            columns={"tag_id", "post_id"})
 *    }
 * )
 * @ORM\Entity(repositoryClass="App\Repository\TagPostRepository")
 */
class TagPost
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Tag")
     * @ORM\JoinColumn(name="tag_id", nullable=false)
     */
    private $tag;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Post")
     * @ORM\JoinColumn(name="post_id", nullable=false)
     */
    private $post;

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

    public function setTag($tag)
    {
        $this->tag = $tag;

        return $this;
    }

    public function getTag()
    {
        return $this->tag;
    }

    public function setPost($post)
    {
        $this->post = $post;

        return $this;
    }

    public function getPost()
    {
        return $this->post;
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
