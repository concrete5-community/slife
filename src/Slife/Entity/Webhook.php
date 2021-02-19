<?php

namespace Slife\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(
 *   name="SlifeWebhooks",
 *   indexes={
 *     @ORM\Index(name="handle", columns={"handle"})
 *   },
 *   uniqueConstraints={@ORM\UniqueConstraint(
 *     name="handle_unique",
 *     columns={"handle"}
 *   )}
 * )
 */
class Webhook
{
    /**
     * @ORM\Id @ORM\Column(type="integer", options={"unsigned":true})
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     */
    protected $isEnabled = true;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $handle;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $uri;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $channel;

    /**
     * @ORM\Column(type="string", nullable=false)
     */
    protected $username;

    /**
     * @return int
     */
    public function getId()
    {
        return (int) $this->id;
    }

    /**
     * @return bool
     */
    public function getIsEnabled()
    {
        return (bool) $this->isEnabled;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->isEnabled;
    }

    /**
     * @return bool
     */
    public function isDisabled()
    {
        return !$this->isEnabled;
    }

    /**
     * @param bool $isEnabled
     */
    public function setIsEnabled($isEnabled)
    {
        $this->isEnabled = (bool) $isEnabled;
    }

    /**
     * @return string
     */
    public function getHandle()
    {
        return (string) $this->handle;
    }

    /**
     * @param string $handle
     */
    public function setHandle($handle)
    {
        $this->handle = is_null($handle) ? null : (string) $handle;
    }

    /**
     * @return string
     */
    public function getUri()
    {
        return trim($this->uri);
    }

    /**
     * @param string $uri
     */
    public function setUri($uri)
    {
        $this->uri = trim($uri);
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return (string) $this->channel;
    }

    /**
     * @param string $channel
     */
    public function setChannel($channel)
    {
        $this->channel = (string) $channel;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return (string) $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = (string) $username;
    }
}
