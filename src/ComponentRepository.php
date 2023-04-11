<?php

namespace Goldnead\StatamicComponent;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Statamic\Support\Str;

/**
 * ComponentRepository class is responsible for managing all components inside resources/components folder.
 *
 * Each component is a folder inside the resources/components folder. The folder name is the component name.
 * It can contain a PHP file with the same name as the folder. This file can be used to configure the component.
 *
 * Right now, the only configuration is the supported types. This is an array of strings.
 * Each string is the name of a fieldset inside resources/fieldsets/components.
 * If the view names differ from the fieldset names, you can use an associative array.
 * When using an associative array, the key is the fieldset name, and the value is the view name.
 *
 * If left empty, the component will use the component name as the fieldset name and view name.
 */
class ComponentRepository
{
    /**
     * An array to store all registered components.
     *
     * @var array
     */
    private $components = [];

    /**
     * ComponentRepository constructor.
     * It initializes the ComponentRepository with all the registered components. Intended to be used
     * as a singleton.
     */
    public function __construct()
    {
        $this->registerComponents($this->all());
    }

    /**
     * Register the components passed as a collection.
     *
     * @param  Collection  $components
     * @return void
     */
    public function registerComponents(Collection $components)
    {
        $components->each(function ($component) {
            $componentName = ucfirst(Str::camel($component));
            $phpFile = components_path($componentName.'/'.$componentName.'.php');
            if (File::exists($phpFile)) {
                include $phpFile;
                $componentNamespace = config('statamic-component.components_namespace') ?? 'App\\Components\\';
                $componentClass = $componentNamespace.$componentName;
                $this->registerComponent($componentName, new $componentClass($componentName));
            } else {
                $this->registerComponent($componentName, new Component($componentName));
            }
        });
    }

    /**
     * This function registers a component of a specified type in an array of components.
     *
     * @param string type A string representing the type of component being registered. For example,
     * "button", "input", "dropdown", etc.
     * @param Component component The  parameter is an instance of the Component class that
     * is being registered. It is being stored in an array called , with the  parameter
     * as the key.
     */
    public function registerComponent(string $type, Component $component)
    {
        $this->components[$type] = $component;
    }

    /**
     * This function finds a component by its name and returns it.
     *
     * @param string componentName A string parameter representing the name of the component to be
     * found.
     * @return Component A `Component` object is being returned.
     */
    public function find(string $componentName): Component
    {
        return $this->components[$componentName];
    }

    /**
     * Returns an array of supported types for a given component name, or an empty
     * array if the component does not exist.
     *
     * @param string componentName A string representing the name of a component.
     * @return array an array of supported types for a given component name. If the component name does
     * not exist, an empty array is returned.
     */
    public function getTypes(string $componentName): array
    {
        if (! $this->exists($componentName)) {
            return [];
        }

        return $this->find($componentName)->getSupportedTypes();
    }

    /**
     * Checks if a component exists by its handle or type.
     *
     * @param string handle A string representing the name or type of a component.
     * @return bool The `exists` function is returning a boolean value (`true` or `false`) depending on
     * whether a component with the given handle exists in the `ComponentRepository::$components` array or can be found by
     * type using the `findComponentByType` method.
     */
    public function exists(string $handle): bool
    {
        $componentName = ucfirst(Str::camel($handle));

        if (array_key_exists($componentName, $this->components)) {
            return true;
        }

        return $this->findComponentByType($handle) !== null;
    }

    /**
     * Finds a component by its type.
     *
     * @param string type The type of component that we want to find.
     * @return ?Component a single instance of a `Component` object that supports the given `$type`
     * parameter. If no such component is found, it returns `null`.
     */
    public function findComponentByType(string $type): ?Component
    {
        $component = collect($this->components)->first(function ($component) use ($type) {
            $types = $this->isAssoc($component->getSupportedTypes()) ? array_keys($component->getSupportedTypes()) : $component->getSupportedTypes();

            return in_array($type, $types);
        });

        return $component;
    }

    /**
     * This function checks if an array is associative or not.
     *
     * @param array
     * @return bool The function isAssoc() is returning a boolean value (true or false) depending on
     * whether the input array is associative or not.
     */
    private function isAssoc(array $array): bool
    {
        // Keys of the array
        $keys = array_keys($array);

        // If the array keys of the keys match the keys, then the array must
        // not be associative (e.g. the keys array looked like {0:0, 1:1...}).
        return array_keys($keys) !== $keys;
    }

    /**
     * This PHP function returns a collection of all directories in a specified local disk.
     *
     * @return Collection The `all()` function is returning a `Collection` of all the directories in
     * the `components` directory using the `Storage` facade to build a local disk instance and
     * retrieve the directories using the `directories()` method.
     */
    public function all(): Collection
    {
        $disk = Storage::build([
            'driver' => 'local',
            'root' => components_path(),
        ]);

        return collect($disk->directories());
    }

    /**
     * This function returns the name of a partial view in a specific format based on certain
     * conditions.
     * Note: this dunction is used ba the Components-Tag.
     *
     * @param string partial The parameter `$partial` is a string that represents the name of the
     * partial view that needs to be rendered.
     * @return string a string that represents the name of the partial view to be rendered.
     */
    public function partialViewName(string $partial): string
    {
        $partials = explode('.', $partial);
        $componentName = count($partials) > 1 ? ucfirst(Str::camel($partials[0])) : ucfirst(Str::camel($partial));
        $partialName = 'template';
        $originalPartial = $partial;

        if (count($partials) > 1) {
            $p = explode('.', $partial);
            unset($p[0]);
            $partialName = implode('.', $p);
        }

        $partial = $componentName.'.views.'.$partialName;

        if (view()->exists($underscored = $this->underscoredViewName($partial))) {
            return $underscored;
        }

        if (view()->exists($subdirectoried = 'partials.'.$partial)) {
            return $subdirectoried;
        }

        if (view()->exists($underscored_subdirectoried = 'partials.'.$this->underscoredViewName($partial))) {
            return $underscored_subdirectoried;
        }

        if ($component = $this->findComponentByType($originalPartial)) {
            return $this->partialViewName($component->viewName($originalPartial));
        }

        return $partial;
    }

    /**
     * This PHP function converts a given view name to an underscored format.
     *
     * @param string partial The parameter is a string representing the name of a view file,
     * which may contain one or more dot-separated segments indicating the directory structure of the
     * view file. For example, a partial view file named `header.antlers.html` located in the `partials`
     * directory would have the partial name
     * @return string a string that is the original `partial` string with the last segment of the
     * string prepended with an underscore. The segments of the string are separated by dots (`.`) and
     * the last segment is identified and prepended with an underscore (`_`).
     */
    protected function underscoredViewName(string $partial): string
    {
        $bits = collect(explode('.', $partial));

        $last = $bits->pull($bits->count() - 1);

        return $bits->implode('.').'._'.$last;
    }
}
