String Blade Compiler
=======================
Render Blade templates from string value.

Reworked version to allow for array to be passed instead of template file name.

This is a direct extension of \Illuminate\View\View and replaces the default.

Version
=======================
This version 1 is for Laravel 4.2, version 2 is for Laravel 5.

Version 3 is a complete rewrite, for Larave 5.1

Installation
=======================

Add the repository to composer.json:

	"repositories": [
	{
	    "name": "wpb/string-blade-compiler",
		"url": "https://github.com/TerrePorter/StringBladeCompiler.git",
		"type": "git"
	}
	],

Add the package to composer.json:

	"require": {
		"laravel/framework": "5.1.*",
		"wpb/string-blade-compiler": "3.*@dev"
	},
	
TODO: add to packagist	

In config\app.php, providers section:

Replace Illuminate\View\ViewServiceProvider::class with Wpb\String_Blade_Compiler\ViewServiceProvider::class,
	
There is no need to add a Facade to the aliases array in the same file as the service provider, this is being included  automatically in the ServiceProvider.

Config
=======================

Default cache time for the compiled string template is 300 seconds (5 mins), this can be changed in the config file or when calling a view. The change is global to all string templates.

Note: If using homestead or some other vm, the host handles the filemtime of the cache file. This means the vm may have a different time than the file. If the cache is not expiring as expected, check the times between the systems.

Usage
=======================

This package offers a StringView facade with the same syntax as View but accepts a Array or Array Object instance instead of path to view.

The array value of 'updated_at' has been removed from this version, a new option called secondsTemplateCacheExpires has been added.

It is number of seconds since the template compiled file was last modified, as so 'time() >= ($this->files->lastModified($compiled) + $viewData->secondsTemplateCacheExpires)'

If cache_key is set, it will be used as the compiled key or a md5(template) is used.

```php

// existing file template load
return view ('bladetemplatefile', ['token' => 'I am the child template']);

// string template load
return view (['template' => '{{$token}}'], ['token' => 'I am the child template']);

// full list of options
return view(
			array(
				// this actual blade template
				'template'  => '{{ $token1 }}',
				// this is the cache file key, converted to md5
				'cache_key' => 'my_unique_cache_key',
				// number of seconds needed in order to recompile, 0 is always recompile
				'secondsTemplateCacheExpires' => 1391973007
			),
			array(
				'token1'=> 'token 1 value'
			)
	);
```

Also allows for Blade::extend, example :

Since the compilers are set up as seperate instances, if you need the extend on both the string and file template you will need to attach the extend (or directive) to both compilers.

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

New options,

```php
	// change the contente tags escaped or not
	StringBlade::setContentTagsEscaped(true);
	
	// for devel force templates to be rebuilt, ignores secondsTemplateCacheExpires
    StringBlade::setForceTemplateRecompile(true);	
```

If you wish to use these with file templates, 

```php
	// change the contente tags escaped or not
	Blade::setContentTagsEscaped(true);
	
	// for devel force templates to be rebuilt, ignores secondsTemplateCacheExpires
    Blade::setForceTemplateRecompile(true);	
```

License
=======================

string-blade-compiler is open-sourced software licensed under the MIT license
