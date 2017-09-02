String Blade Compiler
=======================
[![Laravel 5.2](https://img.shields.io/badge/Laravel-5.2-orange.svg?style=flat-square)](http://laravel.com)
[![License](http://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](https://tldrlegal.com/license/mit-license)

Render Blade templates from string value.

Reworked version to allow for array to be passed to the view function instead of template file name.

This is a direct extension of \Illuminate\View\View and replaces the default.

Version
=======================
This version 1 is for Laravel 4.2, version 2 is for Laravel 5.

Version 3 is a complete rewrite, for Laravel 5.1

Version 3.2 is a version for Laravel 5.2.

Version 3.3 is a version for Laravel 5.3.

Version 3.4 is a version for Laravel 5.4.

Version 3.5 is a version for Laravel 5.5.

Installation
=======================

Add the package to composer.json:

	"require": {
		...
		"wpb/string-blade-compiler": "VERSION"
	},
	
On packagist.org at https://packagist.org/packages/wpb/string-blade-compiler
	
Or from the console using require: composer require "wpb/string-blade-compiler"
 	
To get versions 'composer show wpb/string-blade-compiler', such as 'dev-master, * 3.2.x-dev, 3.2.0, 3.0.x-dev, 3.0.0, 2.1.0, 2.0.x-dev, 2.0.0, 1.0.x-dev, 1.0.0'

In config\app.php, providers section:

Replace 'Illuminate\View\ViewServiceProvider::class' with 'Wpb\String_Blade_Compiler\ViewServiceProvider::class',
	
There is no need to add a Facade to the aliases array as the service provider it is included automatically in the package's ServiceProvider.

Config
=======================

Default cache time for the compiled string template is 300 seconds (5 mins), this can be changed in the config file or when calling a view. The change is global to all string templates.

Note: If using homestead or some other vm, the host handles the filemtime of the cache file. This means the vm may have a different time than the file. If the cache is not expiring as expected, check the times between the systems.

Note: See new option below to delete view cache after rendering (works for both stringblade and blade compilers).

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

Changing the tags

```php
  // change the tags
    StringBlade::setRawTags('[!!', '!!]',escapeFlag);
    StringBlade::setContentTags('[[', ']]',escapeFlag);
    StringBlade::setEscapedContentTags('[[[', ']]]',escapeFlag);
```

'escapeFlag', if true then the tags will be escaped, if false then they will not be escaped (same as setContentTagsEscaped function)

Deleting generated compiled cach view files (v3+),

Set the delete flag for the compiler being used, stringblade or blade
```
// set flag to delete compiled view cache files after rendering for stringblade compiler
View::getEngineFromStringKey('stringblade')->setDeleteViewCacheAfterRender(true);

// set flag to delete compiled view cache files after rendering for blade compiler
View::getEngineFromStringKey('blade')->setDeleteViewCacheAfterRender(true);
```

License
=======================

string-blade-compiler is open-sourced software licensed under the MIT license
