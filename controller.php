<?php

namespace Concrete\Package\Slife;

use Concrete\Core\Package\Package;
use Concrete\Core\Page\Page;
use Concrete\Core\Page\Single;
use Concrete\Core\Support\Facade\Package as PackageFacade;
use Slife\Entity\Webhook;
use Slife\ServiceProvider;

class Controller extends Package
{
    protected $pkgHandle = 'slife';
    protected $appVersionRequired = '8.2';
    protected $pkgVersion = '1.0';
    protected $pkgAutoloaderRegistries = [
        'src/Slife' => '\Slife',
    ];

    public function getPackageName()
    {
        return t('Slife');
    }

    public function getPackageDescription()
    {
        return t('Base package to send messages to Slack.');
    }

    public function on_start()
    {
        $sp = $this->app->make(ServiceProvider::class);
        $sp->register();
    }

    public function install()
    {
        $pkg = parent::install();
        $this->installEverything($pkg);
    }

    public function upgrade()
    {
        $pkg = PackageFacade::getByHandle($this->pkgHandle);
        $this->installEverything($pkg);
    }

    public function installEverything($pkg)
    {
        $this->addDefaultWebhook();
        $this->installPages($pkg);
    }

    /**
     * Slife extensions extend classes in the Slife pkg.
     *
     * Prevent ErrorExceptions by saying that all extensions need to be uninstalled first.
     */
    public function testForUninstall()
    {
        $installedHandles = PackageFacade::getInstalledHandles();
        foreach ($installedHandles as $handle) {
            if ($handle === 'slife') {
                continue;
            }

            if (stripos($handle, 'slife') !== false) {
                $e = $this->app->make('error');
                $e->add(t("Please uninstall all Slife integrations first."));
                return $e;
            }
        }

        return true;
    }

    private function addDefaultWebhook()
    {
        $em = $this->app->make('Doctrine\ORM\EntityManager');

        /** @var Webhook $webhook */
        $webhook = $em
            ->getRepository('\Slife\Entity\Webhook')
            ->findOneByHandle('default');

        // Don't install the webhook twice.
        if ($webhook) {
            return;
        }

        $webhook = new Webhook();
        $webhook->setHandle('default');
        $webhook->setChannel('#general');
        $webhook->setUri('');
        $webhook->setIsEnabled(1);
        $webhook->setUsername($this->getPackageName());

        $em->persist($webhook);
        $em->flush();
    }

    private function installPages($pkg)
    {
        $singlePages = [
            '/dashboard/slife' => 'Slife',
            '/dashboard/slife/webhooks' => t('Webhooks'),
            '/dashboard/slife/messages' => t('Messages'),
        ];

        foreach ($singlePages as $path => $title) {
            /** @var Page $page */
            $page = Page::getByPath($path);
            if ($page && !$page->isError()) {
                continue;
            }

            $singlePage = Single::add($path, $pkg);

            if ($title) {
                $singlePage->update($title);
            }
        }
    }
}
