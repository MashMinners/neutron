<?php

declare(strict_types=1);

namespace Engine\Utilities;

class StringFormatter
{
    public static function camelize(string $input, string $separator = '_', bool $lowerFirst = false) : string
    {
        return lcfirst(str_replace($separator, '', ucwords($input, $separator)));
    }

}