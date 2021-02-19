<?php

namespace Concrete\Package\Slife\Controller\SinglePage\Dashboard\Slife;

use Concrete\Core\Page\Controller\DashboardPageController;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Slife\Entity\Event;
use Slife\Entity\Message;
use Slife\Entity\Webhook;

class Messages extends DashboardPageController
{
    public function view()
    {
        $messages = $this->getMessages();

        $this->set('items', $messages);
        $this->set('eventOptions', $this->getEventOptions());
    }

    public function add()
    {
        if (!$this->request('event_id')) {
            $this->redirect('/dashboard/slife/messages');
        }

        /** @var \Slife\Entity\Event $event */
        $event = $this->entityManager
            ->getRepository('\Slife\Entity\Event')
            ->find($this->request('event_id'));

        if (!$event) {
            $this->redirect('/dashboard/slife/messages');
        }

        $slifeMessage = new \Slife\Entity\Message();
        $slifeMessage->setEvent($event);

        return $this->renderForm($slifeMessage);
    }

    public function edit($id = null)
    {
        if (!$id) {
            $this->redirect('/dashboard/slife/messages');
        }

        $slifeMessage = $this->getMessage($id);

        return $this->renderForm($slifeMessage);
    }

    public function save()
    {
        $token = $this->app->make('token');
        if (!$token->validate('slife_messages_form')) {
            $this->flash('error','Invalid form token');
            $this->redirect('/dashboard/slife/messages');
        }

        if ($this->request('id')) {
            $slifeMessage = $this->getMessage($this->request('id'));
        } else {
            $slifeMessage = new \Slife\Entity\Message();
        }

        // Make sure this message has a valid event attached to it.
        if (!$this->request('event_id')) {
            return $this->redirect('/dashboard/slife/messages');
        }

        /** @var \Slife\Entity\Event $event */
        $event = $this->entityManager
            ->getRepository('\Slife\Entity\Event')
            ->find($this->request('event_id'));

        if (!$event) {
            return $this->redirect('/dashboard/slife/messages');
        }

        $slifeMessage->setEvent($event);

        // Make sure this message has a valid webhook attached to it.
        if (!$this->request('webhook_id')) {
            return $this->redirect('/dashboard/slife/messages');
        }

        /** @var \Slife\Entity\Webhook $webhook */
        $webhook = $this->entityManager
            ->getRepository('\Slife\Entity\Webhook')
            ->find($this->request('webhook_id'));

        if (!$webhook) {
            return $this->redirect('/dashboard/slife/messages');
        }

        $slifeMessage->setWebhook($webhook);
        $slifeMessage->setMessage($this->request('message'));

        try {
            // Add / Update message
            $this->entityManager->persist($slifeMessage);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            $this->error = t('This event already exists for the selected webhook.');

            return $this->request('id') ? $this->edit($this->request('id')) : $this->add();
        }

        $this->flash('success',
            $this->request('id') ?
                t('Message has been saved.') :
                t('Message has been added.')
        );

        return $this->redirect('/dashboard/slife/messages');
    }

    public function delete($id = null)
    {
        if (!$id) {
            $this->redirect('/dashboard/slife/messages');
        }

        $slifeMessage = $this->getMessage($id);
        if (!$slifeMessage) {
            $this->redirect('/dashboard/slife/messages');
        }

        $this->entityManager->remove($slifeMessage);
        $this->entityManager->flush();

        $this->flash('success', t('Message has been removed.'));

        return $this->redirect('/dashboard/slife/messages');
    }

    protected function renderForm(Message $slifeMessage)
    {
        $this->set('slifeMessage', $slifeMessage);
        $this->set('placeholders', $this->getPlaceholders($slifeMessage));

        $webhookOptions = $this->getWebhookOptions();
        $this->set('webhookOptions', $webhookOptions);

        $webhook = $slifeMessage->getWebhook();
        if ($webhook) {
            $this->set('webhookValue', $webhook->getId());
        } else {
            // Make sure the first item is selected if we *add* a message.
            if (count($webhookOptions) === 1) {
                $this->flash('error', t('Please create a webhook first.'));
                $this->redirect('/dashboard/slife/webhooks');
            } elseif (count($webhookOptions) === 2) {
                next($webhookOptions);
                $this->set('webhookValue', key($webhookOptions));
            }
        }

        $this->render('/dashboard/slife/messages/edit');
    }

    /**
     * @param int|null $id
     *
     * @throws \Exception
     *
     * @return Message
     */
    protected function getMessage($id)
    {
        /** @var \Slife\Entity\Message $message */
        $message = $this->entityManager
                ->getRepository('\Slife\Entity\Message')
                ->find($id);

        if (!$message) {
            $this->flash('error', t("This message doesn't exist (anymore)."));
            $this->redirect('/dashboard/slife/messages');
        }

        return $message;
    }

    /**
     * Returns string with all placeholders for a certain message / event.
     *
     * @param Message $message
     *
     * @return string
     */
    protected function getPlaceholders(Message $message)
    {
        return $message->getEvent()->getPlaceholdersAsString();
    }

    /**
     * @return Message[]
     */
    private function getMessages()
    {
        $messages = $this->entityManager
            ->getRepository('\Slife\Entity\Message')
            ->findAll();

        return $messages;
    }

    /**
     * @return array
     */
    private function getEventOptions()
    {
        /** @var Event[] $events */
        $events = $this->entityManager
            ->getRepository('\Slife\Entity\Event')
            ->findAll();

        $options = [];
        $options[''] = t('-- Select an event -- ');
        foreach ($events as $event) {
            $options[$event->getId()] = $event->getEventHandle();
        }

        return $options;
    }

    /**
     * @return array
     */
    private function getWebhookOptions()
    {
        /** @var Webhook[] $webhooks */
        $webhooks = $this->entityManager
            ->getRepository('\Slife\Entity\Webhook')
            ->findAll();

        $options = [];
        $options[''] = t('-- Select a webhook -- ');
        foreach ($webhooks as $webhook) {
            $options[$webhook->getId()] = $webhook->getHandle();
        }

        return $options;
    }
}
