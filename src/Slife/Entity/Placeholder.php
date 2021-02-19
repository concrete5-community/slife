<?php

namespace Slife\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="SlifePlaceholders",
 *   indexes={
 *     @ORM\Index(name="eventId", columns={"eventId"}),
 *     @ORM\Index(name="placeholder", columns={"placeholder"}),
 *   },
 *   uniqueConstraints={@ORM\UniqueConstraint(
 *     name="event_placeholder_unique",
 *     columns={"eventId", "placeholder"}
 *   )}
 * )
 */
class Placeholder
{
    /**
     * @ORM\Id @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="placeholders")
     * @ORM\JoinColumn(name="eventId", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $event;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $placeholder;

    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @return string
     */
    public function getPlaceholder()
    {
        return (string) $this->placeholder;
    }

    /**
     * @param string $placeholder
     */
    public function setPlaceholder($placeholder)
    {
        $this->placeholder = (string) $placeholder;
    }

    /**
     * @return Event
     */
    public function getEvent()
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;
    }
}
