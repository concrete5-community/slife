<?php

namespace Slife;

defined('C5_EXECUTE') or die('Access Denied.');

class Payload
{
    protected $channel;
    protected $username;
    protected $text;
    protected $icon;

    /**
     * Payload constructor.
     *
     * @param string|null $channel
     * @param string|null $username
     * @param string|null $text
     */
    public function __construct($channel = null, $username = null, $text = null)
    {
        $this->channel = $channel;
        $this->username = $username;
        $this->text = $text;
    }

    /**
     * @return string
     */
    public function getChannel()
    {
        return $this->channel ? $this->channel : '#general';
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username ? $this->username : 'Slife';
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text ? $this->text : t('This is a test notification');
    }

    /**
     * @return string
     */
    public function toJson()
    {
        $payload = [
            'channel' => $this->getChannel(),
            'username' => $this->getUsername(),
            'text' => $this->getText(),
            'icon_url' => $this->getIcon(),
        ];

        return json_encode($payload);
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return DIR_PACKAGES.'/slife/icon-for-slack.png';
    }
}
