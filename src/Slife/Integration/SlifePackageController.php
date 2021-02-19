<?php

namespace Slife\Integration;

use Concrete\Core\Package\Package;

class SlifePackageController extends Package
{
    protected $pkgHandle = '';
    protected $supportedEvents = [];

    public function install()
    {
        parent::install();
        $this->installEverything();
    }

    public function upgrade()
    {
        $this->installEverything();
    }

    public function installEverything()
    {
        $this->installEvents();
    }

    protected function installEvents()
    {
        $th = $this->app->make('helper/text');

        foreach ($this->supportedEvents as $eventHandle) {
            $className = $th->camelcase($eventHandle);
            $eventClass = $this->app->make('SlifeC5Events\Event\\'.$className, [
                'package' => $this->getPackageEntity(),
            ]);

            $eventClass->install();
        }
    }
}