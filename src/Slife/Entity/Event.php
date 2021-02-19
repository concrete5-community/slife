<?php

namespace Slife\Entity;

use Doctrine\ORM\Mapping as ORM;
use Concrete\Core\Entity\Package;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="SlifeEvents",
 *   indexes={
 *     @ORM\Index(name="pkgId", columns={"pkgId"}),
 *     @ORM\Index(name="eventHandle", columns={"eventHandle"})
 *   },
 *   uniqueConstraints={@ORM\UniqueConstraint(
 *     name="pkg_event_unique",
 *     columns={"pkgId", "eventHandle"}
 *   )}
 * )
 */
class Event
{
    /**
     * @ORM\Id @ORM\Column(type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\ManyToOne(targetEntity="\Concrete\Core\Entity\Package")
     * @ORM\JoinColumn(name="pkgId", referencedColumnName="pkgID", onDelete="CASCADE")
     */
    protected $package;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $eventHandle;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected $defaultMessage;

    /**
     * @ORM\OneToMany(targetEntity="Placeholder", mappedBy="event", cascade={"remove"})
     */
    protected $placeholders;

    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @param Package|string|int $package
     */
    public function setPackage($package)
    {
        if ($package instanceof Package) {
            $this->package = $package;
        } elseif (is_string($package)) {
            $package = \Concrete\Core\Support\Facade\Package::getByHandle($package);
            self::setPackage($package);
        } elseif (is_int($package)) {
            $package = \Concrete\Core\Support\Facade\Package::getByID($package);
            self::setPackage($package);
        }
    }

    /**
     * @return string
     */
    public function getEventHandle()
    {
        return (string) $this->eventHandle;
    }

    /**
     * @param string $eventHandle
     */
    public function setEventHandle($eventHandle)
    {
        $this->eventHandle = (string) $eventHandle;
    }

    /**
     * @return Placeholder[]
     */
    public function getPlaceholders()
    {
        return $this->placeholders;
    }

    /**
     * @return string
     */
    public function getPlaceholdersAsString()
    {
        $placeholders = [];

        /** @var \Slife\Entity\Placeholder[] $placeholders */
        foreach ($this->getPlaceholders() as $placeholder) {
            $placeholders[] = '{'.$placeholder->getPlaceholder().'}';
        }

        return implode(', ', $placeholders);
    }

    /**
     * @return string
     */
    public function getDefaultMessage()
    {
        return (string) $this->defaultMessage;
    }

    /**
     * @param string $defaultMessage
     */
    public function setDefaultMessage($defaultMessage)
    {
        $this->defaultMessage = (string) $defaultMessage;
    }
}
