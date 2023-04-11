<?php

if (! function_exists('components_path')) {
    function components_path(string $path = ''): string
    {
        return config('statamic-component.components_path').'/'.ltrim($path, '/');
    }
}

if (! function_exists('component_path')) {
    // In case you want to use the same function name as the package
    function component_path(string $path = ''): string
    {
        return components_path($path);
    }
}
