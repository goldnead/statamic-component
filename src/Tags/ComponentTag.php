<?php

namespace Goldnead\StatamicComponent\Tags;

use Goldnead\StatamicComponent\Facades\Component;
use Statamic\Tags\Partial;

/**
 * Extends the `Partial` class provided by Statamic.
 *
 * This class is used to create a custom tag for Statamic that allows developers
 * to easily include partial components in their views located in the configured
 * components path.
 **/
class ComponentTag extends Partial
{
    protected static $handle = 'component';

    /**
     * This function returns the view name of a partial component in PHP.
     *
     * @param partial The parameter "partial" is likely a string that represents the name of a partial
     * view component. The function "viewName" is using this parameter to call the static method
     * "partialViewName" of the "Component" class, which is expected to return the full view name for
     * the given partial view
     * @return The `viewName` function is returning the result of calling the `partialViewName`
     * function of the `Component` class with the `` parameter passed as an argument.
     */
    protected function viewName($partial)
    {
        return Component::partialViewName($partial);
    }

    /**
     * The {{ component:exists }} tag.
     *
     * Returns true if the partial exists, false otherwise.
     * If the src parameter is omitted, it acts like the user is trying to use a partial named "exists".
     */
    public function exists($src = null): bool
    {
        $src = $src ?? $this->params->get('src');

        return Component::exists($src);
    }
}
