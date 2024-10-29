# Paragon

### A tool for automatically generating typescript/javascript objects and utilities based on their PHP counterparts.

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kirschbaum-development/paragon.svg)](https://packagist.org/packages/kirschbaum-development/paragon)
[![Total Downloads](https://img.shields.io/packagist/dt/kirschbaum-development/paragon.svg)](https://packagist.org/packages/kirschbaum-development/paragon)
[![Actions Status](https://github.com/kirschbaum-development/paragon/actions/workflows/tests.yml/badge.svg)](https://github.com/kirschbaum-development/paragon/actions)

## Requirements

| Laravel Version | Paragon Version |
|:----------------|:----------------|
| 11.0            | 1.0.x           |
| 10.0            | 1.0.x           |

## Installation

```bash
composer require kirschbaum-development/paragon
```

## TypeScript Enums

Enums are a fantastic addition to the PHP-verse but are really lame in the TypeScript-verse. However, it can be annoying trying to get those enum values on the
front-end of your project. Are you supposed to pass them as a method when returning a view or perhaps via an API? This
generator solves that problem by scraping your app directory for any and all enums and recreates them as TypeScript
classes so you can import them directly into your Vue, React, or Svelte front-end!

The simplest way to get started is to run the following command:

```bash
php artisan paragon:generate-enums
```

That's it. Now, wherever you may have had enums in your project, "paragons" or near perfect duplicates of those have
been recreated inside of `resources/js/enums`. Here are some examples of the API:

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

### Public Methods

A good majority of the time it is useful to use public methods to return a proper human-readable label or some other functionality on an enum. We've got this covered too. Assuming the following method exists on the above `Status` enum:

```php
public function label(): string
{
    return match ($this) {
        self::Active => 'Active',
        self::Inactive => 'Inactive',
    }; 
}
```

The following method would become accessible using TypeScript:

```ts
Status.Active.label() // 'Active'
Status.Inactive.label() // 'Inactive'
```

### Custom Static Methods

Traits can also be applied to enums that give the extra functionality via static methods. While this package ignores
static methods as it would be pretty difficult to convert PHP code into TypeScript automatically, we allow you to create
files that Paragon will auto-import for you so you can add the same type of functionality on the front-end! Simply run

```bash
php artisan paragon:enum-method
```

You can either let it prompt you for a name or pass it in via the CLI `name` argument. This will create a new file at
`resources/js/vendors/paragon/enums`. You are free to do whatever you need inside this file. You have direct access to
`this.items` which allows you to interact with the enum cases in whatever way you need. Just keep in mind that because
the items are "frozen", you can't mutate them directly.

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
    ...
    
    #[IgnoreParagon]
    public method ignoreMe()
    {
        ...
    }
}
```

### Configuration

You can publish the configuration file by running `php artisan vendor:publish` and locating the Paragon config which will be published at `config/paragon.php`.

### Recommendations

It is recommended that the generated path for the enums is added to the `.gitignore` file. Make sure to run this command during deployment if you do this.

## Automatically Re-generating When Modifying PHP Enums

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
