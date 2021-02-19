<?php

namespace Slife;

use Concrete\Core\Application\Application;
use Concrete\Core\Support\Facade\Config;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Slife\Entity\Webhook;

defined('C5_EXECUTE') or die('Access Denied.');

class Manager
{
    protected $app;
    protected $client;

    /** @var Payload */
    protected $payload;
    protected $entityManager;
    protected $webhook;

    /**
     * Manager constructor.
     *
     * @param Application                       $app
     * @param EntityManagerInterface            $em
     * @param \Concrete\Core\Http\Client\Client $client
     */
    public function __construct(Application $app, EntityManagerInterface $em, \Concrete\Core\Http\Client\Client $client)
    {
        $this->app = $app;
        $this->entityManager = $em;
        $this->client = $client;
    }

    /**
     * @param Webhook|string $webhook
     *
     * @throws Exception
     */
    public function setWebhook($webhook)
    {
        if ($webhook instanceof Webhook) {
            $this->webhook = $webhook;
        } elseif (is_string($webhook)) {
            $this->webhook = $this->getWebhookByHandle($webhook);
        }
    }

    /**
     * @param Payload|string $payload
     * @param null           $webhook
     *
     * @throws Exception
     */
    public function send($payload, $webhook = null)
    {
        $this->setPayload($payload);

        if ($webhook) {
            $this->setWebhook($webhook);
        }

        if (!$this->webhook instanceof Webhook) {
            // Use default hook if none is provided.
            $this->setWebhook('default');
        }

        try {
            if (!$this->webhook instanceof Webhook) {
                throw new Exception(t("Webhook is missing."));
            }

            if ($this->webhook->isDisabled()) {
                return;
            }

            $this->client->setMethod('POST');
            $this->client->setUri($this->webhook->getUri());
            $this->client->setParameterPost($this->getPostParameters());
            $this->client->send();
        } catch (Exception $e) {
            if (Config::get('concrete.debug.display_errors')) {
                throw new Exception($e->getMessage());
            } else {
                /** @var \Concrete\Core\Logging\Logger $log */
                $log = $this->app->make('log/exceptions');
                $log->addError($e->getMessage());
            }
        }
    }

    /**
     * @return array
     */
    protected function getPostParameters()
    {
        return [
            'payload' => $this->payload->toJson(),
        ];
    }

    /**
     * @return Payload
     */
    protected function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param $payload
     */
    private function setPayload($payload)
    {
        if ($payload instanceof Payload) {
            $this->payload = $payload;
        } elseif (is_string($payload)) {
            // We assume payload is a text / message.
            $this->payload = $this->app->make(Payload::class, [
                'text' => $payload,
            ]);
        }
    }

    /**
     * @param string $handle
     *
     * @return Webhook
     *
     * @throws Exception
     */
    private function getWebhookByHandle($handle)
    {
        $r = $this->entityManager->getRepository('\Slife\Entity\Webhook');
        $webhook = $r->findOneByHandle($handle);
        if ($webhook === null) {
            throw new Exception(t("Slife webhook with handle %s has not been found.", $handle));
        }

        return $webhook;
    }
}
