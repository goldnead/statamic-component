<?php

namespace Goldnead\StatamicComponent\Exceptions;

use Exception;

class ComponentNotFoundException extends Exception
{
    public function __construct($component)
    {
        parent::__construct("Component [$component] not found.");
    }
}
