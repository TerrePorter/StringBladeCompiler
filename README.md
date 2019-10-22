String Blade Compiler
=======================
[![Laravel 6](https://img.shields.io/badge/Laravel-6-orange.svg?style=flat-square)](http://laravel.com)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://tldrlegal.com/license/mit-license)

Render Blade templates from string value.

Reworked version to allow for array to be passed to the view function instead of template file name.

> This is a direct extension of \Illuminate\View\View and is build to replace its usage. It will replace the default View instance.

Versions
=======================
| String Blade  | Laravel Version | 
| ------------- |----------------:|
| 4.0           | Laravel 6     |
| 3.8           | Laravel 5.8     |
| 3.7           | Laravel 5.7     |
| 3.6           | Laravel 5.6     |
| 3.5           | Laravel 5.5     |
| 3.4           | Laravel 5.4     |
| 3.3           | Laravel 5.2     |
| 3.2           | Laravel 5.1     |
| 2.*           | Laravel 5       |
| 1.*           | Laravel 4.2     |

Version 3.8 : Updates
=======================
> The package has been completely rewritten, all code updated to be more in line with the Laravel version code. Several of the functions in the extended class were not needed and have been removed and the code has been cleaned up a bit. 

> Also updated the tests to what is available in Laravel View Tests. Some are not applicable to StringBlade.

Changes, 
- Now uses Laravel auto registration feature.
- No long need to remove ```Illuminate\View\ViewServiceProvider::class``` from ```config/app.php```
- The ability to ```setRawTags```, ```setContentTags```, and ```setEscapedTags``` have been removed from Laravel (https://github.com/laravel/framework/issues/17736). They are depreciated here. 
- The compiler function ```setDeleteViewCacheAfterRender```, has been deprecated as I didn't find any code where it was actually being used.
- Added more tests

Installation
=======================

Add the package to composer.json:

	"require": {
		...
		"wpb/string-blade-compiler": "VERSION"
	},
	
> To get versions 'composer show wpb/string-blade-compiler', such as 'dev-master, * 3.2.x-dev, 3.2.0, 3.0.x-dev, 3.0.0, 2.1.0, 2.0.x-dev, 2.0.0, 1.0.x-dev, 1.0.0'

On packagist.org at https://packagist.org/packages/wpb/string-blade-compiler
	
    composer require "wpb/string-blade-compiler"
 	
Configuration
=======================

In config\app.php, providers section:

> Both the ServiceProvider and Facade Alias are auto registered by Laravel. There is no need to add them to the /config/app.php file.

~~Replace 'Illuminate\View\ViewServiceProvider::class' with 'Wpb\String_Blade_Compiler\ViewServiceProvider::class',~~
> This version does not require you to remove the registration of the original view component. Upon ServiceProvider registration it will replace the view binds with its self.

### Laravel's Autoloader

> There currently is a issue in Laravel's preload process of ServiceProviders. Service providers that are registered with the autoloaded are instantiated before service providers that are set in /config/app.php. This may cause problems in prior versions of StringBladeCompiler. The version has been rewitten to account for the autoloading process. 

> A pull request that would load vendor service providers registerd in the ```config/app.php``` file before autoloads, was sent to Laravel/Framework and was rejected. 

* If you have a need to have this, or any other, package load before the vendor autoloads, do this - https://gist.github.com/TerrePorter/4d2ed616c6ebeb371775347f7378c035

Config
=======================

Default cache time for the compiled string template is 300 seconds (5 mins), this can be changed in the config file or when calling a view. The change is global to all string templates.

Note: If using homestead or some other vm, the host handles the filemtime of the cache file. This means the vm may have a different time than the file. If the cache is not expiring as expected, check the times between the systems.

Usage
=======================

This package offers a StringView facade with the same syntax as View but accepts a Array or Array Object instance instead of path to view.

### New Config Option:

Laravel 5.8 BladeCompiler adds a php comment to the compiled template file ```php $contents .= "<?php /**PATH {$this->getPath()} ENDPATH**/ ?>"; ```. Since StringBladeCompiler does not have a "path" aka "template file location" that would be helpful to the developer. I have included a new config value, ```templateRefKey```. This allows the developer to tag the StringBladeCompiler for where it is used. This is for if you end up digging in to the compiled view files, it would allow you to see a tag for StingBladeCompiler files.

### Config Options:

```php
// existing file template load (the original View() method
return view ('bladetemplatefile',['token' => 'I am the token value']);
```
```php
// string blade template load
return view (['template' => '{{$token}}'], ['token' => 'I am the token value']);
```

```php
// you can mix the view types
$preset = view (['template' => '{{$token}}'], ['token' => 'I am the token value']);

return view ('bladetemplatefile', ['token' => $preset]);
```

```php
// full list of options
return view(
            array(
                // this actual blade template
                'template'  => '{{ $token1 }}',

                // this is the cache file key, converted to md5
                'cache_key' => 'my_unique_cache_key',

                // number of seconds needed in order to recompile, 0 is always recompile
                'secondsTemplateCacheExpires' => 1391973007,

                // sets the PATH comment value in the compiled file
                'templateRefKey' => 'IndexController: Build function'
            ),
            array(
                'token1'=> 'token 1 value'
            )
        );
```

> Since StringBlade is a extend class from the original View. You should be able to do anything you would normally do with a View using StringBlade.

### Blade::extend, for example :

As the compilers are set up as separate instances, if you need the extend on both the string and file template you will need to attach the extend (or directive) to both compilers.

```php
// allows for @continue and @break in foreach in blade templates
StringBlade::extend(function($value)
{
    return preg_replace('/(\s*)@(break|continue)(\s*)/', '$1<?php $2; ?>$3', $value);
});

Blade::extend(function($value)
{
    return preg_replace('/(\s*)@(break|continue)(\s*)/', '$1<?php $2; ?>$3', $value);
});
```

### Other options,

```php
// change the contente tags escaped or not
StringBlade::setContentTagsEscaped(true);

// for devel force templates to be rebuilt, ignores secondsTemplateCacheExpires
StringBlade::setForceTemplateRecompile(true);	
```

```php
// change the contente tags escaped or not
Blade::setContentTagsEscaped(true);
	
// for devel force templates to be rebuilt, ignores secondsTemplateCacheExpires
Blade::setForceTemplateRecompile(true);	
```

```php
// @deprecated This feature was removed from Laravel (https://github.com/laravel/framework/issues/17736)

// change the tags
StringBlade::setRawTags('[!!', '!!]',escapeFlag); // default {!! !!}
StringBlade::setContentTags('[[', ']]',escapeFlag); // default {{ }}
StringBlade::setEscapedTags('[[[', ']]]',escapeFlag); // default {{{ }}}

__ Functions are still there, use at your own risk. __
```

~~Deleting generated compiled cach view files (v3+).Set the delete flag for the compiler being used, stringblade or blade~~

> I can't seem to find when the setting was actually used. If you want this, submit a bug and I will see about adding the ability.

```php
// set flag to delete compiled view cache files after rendering for stringblade compiler
View::getEngineFromStringKey('stringblade')->setDeleteViewCacheAfterRender(true);

// set flag to delete compiled view cache files after rendering for blade compiler
View::getEngineFromStringKey('blade')->setDeleteViewCacheAfterRender(true);
```

License
=======================

string-blade-compiler is open-sourced software licensed under the MIT license
