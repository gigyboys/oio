<?php

namespace App\Model;


class MailMessage
{
    private $subject;
    private $body;
    private $from;
    private $to;
    private $replyTo;
    private $wrap;
    private $attachments;

    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject): void
    {
        $this->subject = $subject;
    }

    public function getBody()
    {
        return $this->body;
    }

    public function setBody($body): void
    {
        $this->body = $body;
    }

    public function getFrom()
    {
        return $this->from;
    }

    public function setFrom($from): void
    {
        $this->from = $from;
    }

    public function getTo()
    {
        return $this->to;
    }

    public function setTo($to): void
    {
        $this->to = $to;
    }

    public function getReplyTo()
    {
        return $this->replyTo;
    }

    public function setReplyTo($replyTo): void
    {
        $this->replyTo = $replyTo;
    }

    public function getWrap()
    {
        return $this->wrap;
    }

    public function setWrap($wrap): void
    {
        $this->wrap = $wrap;
    }

    public function getAttachments()
    {
        return $this->attachments;
    }

    public function setAttachments($attachments): void
    {
        $this->attachments = $attachments;
    }
}