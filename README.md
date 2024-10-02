# Paragon

A tool for automatically generating Typescript Enums (that don't suck) from your PHP Enums.

## Automatically Re-generating When Modifying PHP Enums

Install the [`vite-plugin-watch`](https://www.npmjs.com/package/vite-plugin-watch) plugin in your project via `npm`:

```shell
npm i -D vite-plugin-watch
```

In your `vite.config.js` file, import the plugin, and add the plugin paramaters to your plugins array:

```js
import { watch } from "vite-plugin-watch"

export default defineConfig({
  plugins: [
    // ...
 
    watch({
      pattern: "app/Enums/**/*.php",
      command: "php artisan paragon:generate-enums",
    }),
  ],
})
```
