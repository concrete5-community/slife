<?php

namespace Concrete\Package\Slife\Controller\SinglePage\Dashboard\Slife;

use Concrete\Core\Page\Controller\DashboardPageController;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Slife\Entity\Webhook;
use Slife\Payload;
use Slife\Support\Facade\Slife;

class Webhooks extends DashboardPageController
{
    public function view()
    {
        $this->set('items', $this->getWebhooks());
        $this->set('webhookOptions', $this->getWebhookOptions());
    }

    public function add()
    {
        $webhook = new Webhook();
        $webhook->setChannel('#general');
        $webhook->setUsername('Slife');

        return $this->renderForm($webhook);
    }

    public function edit($id = null)
    {
        if (!$id) {
            $this->redirect('/dashboard/slife/webhook');
        }

        $webhook = $this->getWebhook($id);

        return $this->renderForm($webhook);
    }

    public function save()
    {
        $token = $this->app->make('token');
        if (!$token->validate('slife_webhooks_form')) {
            $this->flash('error','Invalid form token');
            $this->redirect('/dashboard/slife/webhooks');
        }

        if ($this->request('id')) {
            $webhook = $this->getWebhook($this->request('id'));
        } else {
            $webhook = new Webhook();
        }

        $webhook->setIsEnabled($this->request('isEnabled'));
        $webhook->setUri($this->request('uri'));
        $webhook->setChannel($this->request('channel'));
        $webhook->setUsername($this->request('username'));
        $webhook->setHandle($this->request('handle'));

        try {
            // Add / Update webhook
            $this->entityManager->persist($webhook);
            $this->entityManager->flush();
        } catch (UniqueConstraintViolationException $e) {
            $this->error = t('This handle is already in use.');

            return $this->request('id') ? $this->edit($this->request('id')) : $this->add();
        }

        $this->flash('success',
            $this->request('id') ?
                t('Webhook has been saved.') :
                t('Webhook has been added.')
        );

        return $this->redirect('/dashboard/slife/webhooks');
    }

    /**
     * Sends a test notification to Slack.
     *
     * @param int $id
     */
    public function test($id = null)
    {
        if (!$id) {
            $this->redirect('/dashboard/slife/webhook');
        }

        $webhook = $this->getWebhook($id);
        /** @var Payload $payload */
        $payload = $this->app->make(Payload::class, [
            'text' => t('Test message!'),
            'channel' => $webhook->getChannel(),
            'username' => $webhook->getUsername(),
        ]);

        Slife::send($payload, $webhook);

        $this->flash('success', 'A test message has been sent to Slack.');

        return $this->redirect('/dashboard/slife/webhooks');
    }

    public function delete($id = null)
    {
        if (!$id) {
            $this->redirect('/dashboard/slife/webhooks');
        }

        $webhook = $this->getWebhook($id);
        if (!$webhook) {
            $this->redirect('/dashboard/slife/webhooks');
        }

        $this->entityManager->remove($webhook);
        $this->entityManager->flush();

        $this->flash('success', t('Webhook has been removed.'));

        return $this->redirect('/dashboard/slife/webhooks');
    }

    protected function renderForm(Webhook $webhook)
    {
        $this->set('token', $this->app->make('token'));
        $this->set('webhook', $webhook);

        $this->render('/dashboard/slife/webhooks/edit');
    }

    /**
     * @return Webhook[]|null
     */
    protected function getWebhooks()
    {
        $webhooks = $this->entityManager
            ->getRepository('\Slife\Entity\Webhook')
            ->findAll();

        return $webhooks;
    }

    /**
     * @return array
     */
    protected function getWebhookOptions()
    {
        $options = [];

        foreach ($this->getWebhooks() as $webhook) {
            $options[] = $webhook->getUri();
        }

        return $options;
    }

    /**
     * @param int|null $id
     *
     * @throws \Exception
     *
     * @return Webhook
     */
    protected function getWebhook($id)
    {
        /** @var Webhook $webhook */
        $webhook = $this->entityManager
            ->getRepository('\Slife\Entity\Webhook')
            ->find($id);

        if (!$webhook) {
            $this->flash('error', t("This webhook doesn't exist (anymore)."));
            $this->redirect('/dashboard/slife/webhooks');
        }

        return $webhook;
    }
}
