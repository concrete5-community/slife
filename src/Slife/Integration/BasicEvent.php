<?php

namespace Slife\Integration;

use Concrete\Core\Application\Application;
use Concrete\Core\Entity\Package;
use Doctrine\ORM\EntityManager;
use Exception;
use Slife\Entity\Event;
use Slife\Entity\Message;
use Slife\Entity\Placeholder;
use Slife\Manager;
use Slife\Payload;
use Slife\Utility\Slack;

/**
 * Class BasicEvent.
 *
 * This class can be extended by Slife integration add-ons.
 * Say you have an on_product_sale event, you call the class OnProductSale and
 * make it extend this class.
 *
 * Please provide the package parameter (package entity) if you extend from a package.
 */
abstract class BasicEvent
{
    /**
     * @var Application
     */
    protected $app;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Package|null
     */
    protected $package;

    /** @var Event|null the Slife Event */
    protected $slifeEvent;

    public function __construct(Application $app, EntityManager $entityManager, $package = null)
    {
        if (get_called_class() === 'BasicEvent') {
            throw new \Exception(t("You are not allowed to instantiate the abstract BasicEvent class"));
        }

        $this->app = $app;
        $this->entityManager = $entityManager;
        $this->package = $package;
    }

    /**
     * Return the handle of the event.
     *
     * E.g. 'on_page_add'.
     *
     * @return string
     */
    public function getEventHandle()
    {
        $th = $this->app->make('helper/text');
        $className = (new \ReflectionClass($this))->getShortName();

        return $th->uncamelcase($className);
    }

    /**
     * Runs when event is triggered.
     *
     * @param $concreteEvent Event the triggered event
     */
    public function run($concreteEvent)
    {
        try {
            $slifeMessage = $this->getMessage();
            if (!$slifeMessage) {
                // No message has been added via the dashboard.
                // The event has not been 'activated'.
                return;
            }

            // Get a Slife instance
            /** @var Manager $slife */
            $slife = $this->app->make('slife');

            $messageText = $this->runPlaceholders($concreteEvent, $slifeMessage);
            $webhook = $slifeMessage->getWebhook();

            // Create a custom Slack message
            $payload = $this->app->make(Payload::class, [
                'channel' => $webhook->getChannel(),
                'username' => $webhook->getUsername(),
                'text' => $messageText,
            ]);

            $slife->send($payload, $webhook);
        } catch (Exception $e) {
            /** @var \Concrete\Core\Logging\Logger $log */
            $log = $this->app->make('log/exceptions');
            $log->addError($e->getMessage());
        }
    }

    /**
     * Replace placeholders in message text.
     *
     * @param $concreteEvent mixed likely an instance of GenericEvent
     * @param Message $slifeMessage
     *
     * @return mixed|string
     */
    protected function runPlaceholders($concreteEvent, Message $slifeMessage)
    {
        $slifeEvent = $slifeMessage->getEvent();
        $placeholders = $slifeEvent->getPlaceholders();

        $th = $this->app->make('helper/text');
        $message = $slifeMessage->getMessage();
        foreach ($placeholders as $placeholder) {
            // E.g. 'replaceFirstName'
            $method = 'replace'.$th->camelcase($placeholder->getPlaceholder());
            if (!is_callable([$this, $method])) {
                continue;
            }

            $message = call_user_func_array([$this, $method], [
                'event' => $concreteEvent,
                'message' => $message,
            ]);
        }

        return $message;
    }

    /**
     * Please override this in child classes.
     */
    protected function getDefaultMessage()
    {
        return '';
    }

    /**
     * Utility method to add an event to Slife.
     *
     * It will basically add a record to the SlifeEvents table.
     *
     * @return Event
     *
     * @throws \Exception
     */
    protected function getOrCreateEvent()
    {
        $this->slifeEvent = $this->getSlifeEvent();
        if ($this->slifeEvent) {
            return $this->slifeEvent;
        }

        // Create an event
        /** @var Event $event */
        $event = $this->app->make(\Slife\Entity\Event::class);
        if ($this->package instanceof Package) {
            $event->setPackage($this->package);
        }
        $event->setDefaultMessage($this->getDefaultMessage());
        $event->setEventHandle(static::getEventHandle());

        // Save the event
        $this->entityManager->persist($event);
        $this->entityManager->flush();

        $this->slifeEvent = $event;

        return $this->slifeEvent;
    }

    /**
     * @throws \Exception
     *
     * @return Event|null
     */
    protected function getSlifeEvent()
    {
        if (!static::getEventHandle()) {
            throw new \Exception(t('Invalid Event Handle'));
        }

        if ($this->slifeEvent) {
            return $this->slifeEvent;
        }

        return $this->entityManager->getRepository('\Slife\Entity\Event')->findOneBy([
            'package' => ($this->package instanceof Package) ? $this->package : null,
            'eventHandle' => static::getEventHandle(),
        ]);
    }

    /**
     * @param array $placeholders
     */
    protected function getOrCreatePlaceholders(array $placeholders)
    {
        foreach ($placeholders as $placeholder) {
            $this->createPlaceholder($placeholder);
        }
    }

    /**
     * Utility method to add a placeholder to Slife.
     *
     * It will basically add a record to the SlifePlaceholders table.
     * Each event can have zero or more placeholders. Make sure each placeholder
     * has a method in your Event class that handles the substitution.
     *
     * E.g. if you placeholder is called 'first_name', add a method as follows:
     * protected function replaceFirstName($event, $message)
     * Where $message is a string. (can't do typehint because of PHP 5.6).
     *
     *
     * @param string $name
     *
     * @return Placeholder
     */
    protected function createPlaceholder($name)
    {
        $event = $this->getSlifeEvent();

        /** @var Placeholder|null $placeholder */
        $placeholder = $this->entityManager->getRepository('\Slife\Entity\Placeholder')->findOneBy([
            'event' => $event,
            'placeholder' => $name,
        ]);

        if ($placeholder) {
            return $placeholder;
        }

        // Create a placeholder
        $placeholder = new \Slife\Entity\Placeholder();
        $placeholder->setPlaceholder($name);
        $placeholder->setEvent($event);

        // Save the placeholder
        $this->entityManager->persist($placeholder);
        $this->entityManager->flush();

        return $placeholder;
    }

    /**
     * @return Message|null
     */
    protected function getMessage()
    {
        $event = $this->getSlifeEvent();

        /** @var Message|null $message */
        $message = $this->entityManager->getRepository('\Slife\Entity\Message')->findOneBy([
            'event' => $event,
        ]);

        return $message;
    }
}
