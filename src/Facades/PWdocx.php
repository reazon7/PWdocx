<?php

namespace REAZON\PWdocx\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \REAZON\PWdocx\PWdocxClient from(string $fileName, string $parentDir = null)
 */
class PWdocx extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'pwdocx';
    }
}
