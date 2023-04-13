# Statamic Component

> Statamic-Component is an addon to simplify the creation of separated components in Statamic that combines a bunch of logic like fieldsets & views.

This is for developers who love things DRY. The folder structure in Statamic is usually pretty simple to understand, but as soon
a project grows, it can become a bit of a mess. With this addon you are able to group your component files into a single folder.

## Features

The aim of this addon was to make statamic projects clearer and more organized. It does this by grouping all the files for a component into a single folder. This makes it easier to find and edit files for a component.

A Component-Folder can include:

- Antlers or Blade Templates
- Stylesheets
- Javascripts
- Fieldsets

Note: Stylesheets and Javascript files are not included by this addon as they are likely part of your own
build process.

## How to Install

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

```bash
composer require goldnead/statamic-component
```

## How to Use

### Creating a Component

To create a component, simply create a folder in your `resources/components` folder. The name of the folder will be the name of the component.

Inside the folder, you can add any of the following files/folders:

- `views` - This folder can contain any number of Antlers or Blade templates. If you only have one file you might want to name it
  `_template.antlers.html` or `_template.blade.php`.
- `fieldsets` - This folder can contain any number of fieldsets. Fieldsets are automatically namespaced to the component name. You can find and edit them in the `Fieldsets` section of the control panel, just as you would with regular fieldsets.
- `<Component-Name>.php` - This file is the main component file. It must be named the same as the folder in CamelCase.

### Using a Component

To use a component, you can use the `{{ component }}` tag the same way as you would use the `{{ partial }}` tag. In fact, the `{{ component }}` tag is just an extension of the `{{ partial }}` tag:

```antlers
{{ component:audio-player }} {{# this will use the view located in 'resources/components/AudioPlayer/views/_template.antlers.html' #}}
```

Alternatively, you can use the `{{ component }}` tag with the `src` parameter:

```antlers
{{ component src="audio-player" }}
```

Honeslty, that's all there is to it. You can use the `{{ component }}` tag just like you would use the `{{ partial }}` tag.

## Configuration

### Config File

You can publish the config file by running the following command from your project root:

```bash
php please vendor:publish --tag=statamic-component
```

This will create a `statamic-component.php` file in your `config/Statamic` folder. The config file contains the following options:

- `components_path` - The path to the components folder. This is relative to the project root. The default is `resources/components`. You'll also have a handy `component_path()` helper function to use in your PHP code.
- `component_namespace` - This is the namespace to your components directory. This is used to autoload your components and is only used when you use a custom component Class to [extend the Component class inside of your component](#component-class). The default is `App\\Components`.

### Fieldset Namespacing and Fieldset-Types

#### Namespacing

By default, fieldsets are namespaced to the component name. This means that if you have a component called `audio-player` and a fieldset called `settings`, the fieldset will be namespaced to `audio-player.settings`.

#### Fieldset-Types

Fieldset types are a way to use fieldsets danymically like you would inside of a Replicator or Bard set:

```antlers
{{ article }}
  {{ component :src="type" }}
{{ /article }}
```

For example, if you have a component called `Audio` which has a fieldsets called
`track` and `playlist` which are both tied to different views, you can use this approach to avoid polluting your templates with the logic of which views to connect to your views.
You can configure this inside of your `<Component-Name>.php` file:

```php
<?php

namespace App\Components;

use Goldnead\StatamicComponent\Component;

class Audio extends Component
{
    public $fieldsetTypes = [
        // connects the 'audio_track' fieldset to the 'player' view
        'audio_track' => 'player',
        // connects the 'audio_playlist' fieldset to the 'playlist' view
        'audio_playlist' => 'playlist',
    ];
}
```

This way, you can define your article Fieldset/Blueprint like this:

```yaml
title: Article
fields:
  -
    handle: article
    field:
      ...
      sets:
        audio_playlist:
          display: 'Audio Playlist'
          fields:
            -
              import: 'audio::playlist' # use :: to tell statamic to look for a fieldset inside of specific namespace
        audio_track:
          display: 'Audio Track'
          fields:
            -
              import: 'audio::track'
```

## Frontend Build

As stated before, this addon does not include any stylesheets or javascript build processes. This is because you are likely using your own build process.

However, here's a quick example of how you can use this addon to create a component with a stylesheet and javascript file with vite:

```js
// resources/js/site.js
import * as AudioPlayer from "../components/Audio/Player";

document.addEventListener("DOMContentLoaded", () => {
  AudioPlayer.init();
});

// resources/components/Audio/Player.js

import "./styles/_player.scss";

export function init() {
  console.log("Audio Player initialized");
}
```
