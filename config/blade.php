<?php

return [
    // How many seconds past compiled file last modified time to recompile the template
    // A value of 0 is always recompile
    // Note: homestead time verses pc time may be off
    'secondsTemplateCacheExpires' =>  env('STRING_BLADE_CACHE_TIMEOUT', 300),

    // Determine whether the service provider to autoload blade custom directives.
    'autoload_custom_directives' => env('STRING_BLADE_AUTOLOAD', false),
];