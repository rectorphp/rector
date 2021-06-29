<?php

namespace Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

final class OnCannotGetScopeOfFunctionCall
{
    public static function streamSupports(string $function, $ret, $resource): bool
    {
        define('ENVIRONMENT', 'testing');

        if (ENVIRONMENT === 'testing') {
            return false;
        }

        return function_exists($function, 'abc') && @$function($resource);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php71\Rector\FuncCall\RemoveExtraParametersRector\Fixture;

final class OnCannotGetScopeOfFunctionCall
{
    public static function streamSupports(string $function, $ret, $resource): bool
    {
        define('ENVIRONMENT', 'testing');

        if (ENVIRONMENT === 'testing') {
            return false;
        }

        return function_exists($function) && @$function($resource);
    }
}

?>