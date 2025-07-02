# Paragon

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kirschbaum-development/paragon.svg)](https://packagist.org/packages/kirschbaum-development/paragon)
[![Total Downloads](https://img.shields.io/packagist/dt/kirschbaum-development/paragon.svg)](https://packagist.org/packages/kirschbaum-development/paragon)
[![Actions Status](https://github.com/kirschbaum-development/paragon/actions/workflows/tests.yml/badge.svg)](https://github.com/kirschbaum-development/paragon/actions)

A tool for automatically generating typescript/javascript objects and utilities based on their PHP counterparts.

## Table of Contents

- [Requirements](#requirements)
- [Installation](#installation)
- [Enums](#enums)
  - [Custom Enum Methods](#custom-enum-methods)
  - [Ignoring Enums or Methods](#ignoring-enums-or-public-methods)
  - [Configuration](#configuration)
  - [Recommendations](#recommendations)
  - [Automatically Regenerate](#automatically-re-generating-when-modifying-php-enums)
  - [Technical Details](#technical-details-)
- [Events](#broadcast-events)
  - [Usage](#usage)


## Requirements

| Laravel Version | Paragon Version |
|:----------------|:----------------|
| 12.0            | ^1.0           |
| 11.0            | ^1.0           |
| 10.0            | ^1.0            |

## Installation

```bash
composer require kirschbaum-development/paragon
```

## Enums

**TL;DR:** Run the following command to generate Typescript (or Javascript) enums from your PHP enums:

```bash
php artisan paragon:enum:generate
```

That's it. Now, wherever you may have had enums in your project, "paragons" or near perfect duplicates of those have been recreated inside of `resources/js/enums`. Here are some examples of the API:

```php PHP API
use App\Enums\Status;

Status::Active;
Status::Active->value;
Status::cases();
Status::from('active'); 
Status::tryFrom('active'); 
```

```ts TypeScript API
import Status from '@/js/enums/Status.ts';

Status.Active;
Status.Active.value;
Status.cases();
Status.from('active');
Status.tryFrom('active'); 
```

As you can see the API is nearly the same, the only difference being how the two languages expect you to access objects!

**Generating javascript enums**

This package also supports generating Javascript enums. To do so, simply pass the `--javascript` flag to the command:

```bash
php artisan paragon:enum:generate --javascript
```

#### Public Methods

A good majority of the time it is useful to use public methods to return a proper human-readable label or some other functionality on an enum. Paragon got this covered too. Assuming the following method exists on the above `Status` enum:

```php
public function label(): string
{
    return match ($this) {
        self::Active => 'Active',
        self::Inactive => 'Inactive',
    }; 
}

public function color(): string
{
    return match ($this) {
        self::Active => 'bg-green-100',
        self::Inactive => 'bg-red-100',
    }; 
}
```

The following method would become accessible using TypeScript:

```ts
Status.Active.label() // 'Active'
Status.Inactive.label() // 'Inactive'
Status.Active.color() // 'bg-green-100'
Status.Inactive.color() // 'bg-red-100'
```

### Custom Enum Methods

While this package ignores static methods on the PHP Enums, we allow you to create additional methods that Paragon will make available for every generated Enum.

```bash
php artisan paragon:enum:add-method
```

You will be prompted to search for the enum this method should belong to. It will be placed inside a directory that matches the namespace of the enum. For example, an enum at `App\Enums\Status` would place an enum method file at `resources/js/vendors/paragon/enums/App/Enums/Status`. You are free to do whatever you need inside this file. You have direct access to `this.items` which allows you to interact with the enum cases in whatever way you need. Just keep in mind that because the items are "frozen", you can't mutate them directly. An example would be to have a method that automatically generates a select list from your Enum.

If you need to create a method that is available to every enum, just create an enum method with the `--global` or `-g` flag.

```bash
php artisan paragon:enum-method --global
```

This will create a new file at `resources/js/vendors/paragon/enums` containing a method.

### Ignoring Enums Or Public Methods

There may be enums or enum methods that you don't want inside your automatically generated code. If this is the case simply use the `IgnoreParagon` attribute.

```php
use Kirschbaum\Paragon\Concerns\IgnoreParagon;

#[IgnoreParagon]
enum IgnoreMe
{
    case Ignored;
}
```

```php
use Kirschbaum\Paragon\Concerns\IgnoreParagon;

enum Status
{
    // ...
    
    #[IgnoreParagon]
    public method ignoreMe()
    {
        // ...
    }
}
```

### Configuration

You can publish the configuration file by running `php artisan vendor:publish` and locating the Paragon config which will be published at `config/paragon.php`.

### Recommendations

It is recommended that the generated path for the enums is added to the `.gitignore` file. Make sure to run this command during deployment if you do this.

### Automatically Re-generating When Modifying PHP Enums

Install the [`vite-plugin-watch`](https://www.npmjs.com/package/vite-plugin-watch) plugin in your project via `npm`:

```shell
npm i -D vite-plugin-watch
```

In your `vite.config.js` file, import the plugin, and add the plugin paramaters to your plugins array:

```js
import { watch } from "vite-plugin-watch";

export default defineConfig({
    plugins: [
        // ...
        
        watch({
            pattern: "app/Enums/**/*.php",
            command: "php artisan paragon:generate-enums",
        }),
    ],
});
```

### Technical Details 🤓

Enums are a fantastic addition to the PHP-verse but are really lame in the TypeScript-verse. However, it can be annoying trying to get those enum values on the
front-end of your project. Are you supposed to pass them as a method when returning a view or perhaps via an API? This
generator solves that problem by scraping your app directory for any and all enums and recreates them as TypeScript
classes so you can import them directly into your Vue, React, or Svelte front-end!

Let's take a closer look at a simple PHP enum and its generated Typescript code.

```php
namespace App\Enums;

enum Status: string
{
    case Active = 'active';
    case Inactive = 'inactive';
}
```

```ts
import Enum from '../Enum.ts';

type StatusDefinition = {
    name: string;
    value: string;
};

class Status extends Enum {
    protected static items = Object.freeze({
        Active: Object.freeze({
            name: 'Active',
            value: 'active',
        }),
        Inactive: Object.freeze({
            name: 'Inactive',
            value: 'inactive',
        }),
    });

    public static get Active(): AlarmStatusDefinition {
        return this.items['Active'];
    }

    public static get Inactive(): AlarmStatusDefinition {
        return this.items['Inactive'];
    }
}

export default Status;
```

At first glance it appears as though a lot more stuff is happening, but the above generated code allows us to interact
with the enum in a nearly identical way as in PHP. And you may notice the generated TypeScript class extends the `Enum`
class. This gives us some underlying functionality that is available to every enum.

## Broadcast Events

**TL;DR:** Run the following command to generate a Typescript (or Javascript) broadcast event object based on your broadcastable events:

```bash
php artisan paragon:event:generate
```

Any events within the `App\Events` namespace will be automatically added to the `Events` object. Just make sure your event implements the `ShouldBroadcast` contract!

If javascript is needed, just make sure to add the `--javascript` or `-j` flag.

### Usage

The primary usage of this tool is for Laravel Echo. Listening to for an event class based on a hard-coded string is not exactly preferable. The can easily get out of sync or just get mistyped.

Here is a quick example event:

```php
namespace App\Events;

class ParagonGenerated implements ShouldBroadcast
{ 
    // ...
}
```

Then listen for it with Echo:

```js
import Events from '@/events/Events.ts';

Echo.channel(...)
    .listen(Events.ParagonGenerated, /* ... */);
```

If you have defined a `broadcastOn` method that returns a custom name, this will be handled correctly for you as well.

One thing to note is that if an event namespace starts with `App\Events` or `App`, these will be removed from the dot path within the object. If an event is nested, the dot path will reflect this: For example:

```php
namespace App\Support;

class NestedEvent implements ShouldBroadcast
{ 
    // ...
}
```

```js
import Events from '@/events/Events.ts';

Echo.channel(...)
    .listen(Events.Support.NestedEvent, /* ... */);
```