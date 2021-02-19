<?php

namespace Slife\Support\Facade;

use Concrete\Core\Support\Facade\Facade;

/**
 * @see \Slife\Manager
 */
class Slife extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'slife';
    }
}
