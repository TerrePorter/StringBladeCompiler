String Blade Compiler
=======================
Render Blade templates from string value.

This is a fork from https://github.com/Flynsarmy/laravel-db-blade-compiler which uses Elequent model to pass in a template.

I have reworked it to allow for a generic array of the required fields to generates and return a compiled view from a blade-syntax template.

Installation
=======================

(I'm unsure how to move the code from laravel 4 workbench to vendor)

My current path is app\workbench\wpb\string-blade-compiler

Add the ServiceProvider to the providers array in app/config/app.php

'Wpb\StringBladeCompiler\StringBladeCompilerServiceProvider',

There is no need to add a Facade to the aliases array in the same file as the service provider, this is being included  automatically in the ServiceProvider.

Usage
=======================

This package offers a StringView facade with the same syntax as View but accepts a Array or Array Object instance instead of path to view.

```php
return StringView::make(
                        array(
                            // this actual blade template
                            'template'  => '{{ $token1 }}',
                            // this is the cache file key, converted to md5
                            'cache_key' => 'my_unique_cache_key',
                            // timestamp for when the template was last updated, 0 is always recompile
                            'updated_at' => 1391973007
                        ),
                        array(
                            'token1'=> 'token 1 value'
                        )
                );
```

License
=======================

string-blade-compiler is open-sourced software licensed under the MIT license
