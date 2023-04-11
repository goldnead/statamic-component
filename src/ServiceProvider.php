<?php

namespace Goldnead\StatamicComponent;

use Illuminate\Support\Facades\Storage;
use Statamic\Facades\Fieldset;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $tags = [
        \Goldnead\StatamicComponent\Tags\ComponentTag::class,
    ];

    public function bootAddon()
    {
        /** Registering a singleton instance of the `ComponentRepository` class within the Laravel application container. */
        $this->app->singleton(ComponentRepository::class, function () {
            return new ComponentRepository;
        });

        $disk = Storage::build([
            'driver' => 'local',
            'root' => components_path(),
        ]);

        $components = collect($disk->directories());

        $components->each(function ($component) use ($disk) {
            $component = basename($component);
            $hasFieldsets = $disk->exists("$component/fieldsets");

            if ($hasFieldsets) {
                Fieldset::addNamespace(strtolower($component), components_path("$component/fieldsets"));
            }
        });
    }
}
