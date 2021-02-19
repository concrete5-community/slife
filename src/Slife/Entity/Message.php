<?php

namespace Slife\Entity;

use Doctrine\ORM\Mapping as ORM;
use Exception;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="SlifeMessages",
 *   uniqueConstraints={@ORM\UniqueConstraint(
 *     name="event_webhook_unique",
 *     columns={"eventId", "webhookId"}
 *   )}
 * )
 */
class Message
{
    /**
     * @ORM\Id @ORM\Column(type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="text", nullable=false)
     */
    protected $message;

    /**
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(
     *     name="eventId",
     *     referencedColumnName="id",
     *     onDelete="CASCADE"
     * )
     */
    protected $event;

    /**
     * @ORM\ManyToOne(targetEntity="Webhook")
     * @ORM\JoinColumn(
     *     name="webhookId",
     *     referencedColumnName="id",
     *     nullable=false,
     *     onDelete="CASCADE"
     * )
     */
    protected $webhook;

    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @return Event
     *
     * @throws Exception
     */
    public function getEvent()
    {
        if (!$this->event) {
            throw new Exception('Event missing');
        }

        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return (string) $this->message;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = (string) $message;
    }

    /**
     * @return Webhook
     */
    public function getWebhook()
    {
        return $this->webhook;
    }

    /**
     * @param Webhook $webhook
     */
    public function setWebhook(Webhook $webhook)
    {
        $this->webhook = $webhook;
    }
}
