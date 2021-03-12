<?php

namespace Rector\Tests\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

final class SuperGlobal
{
    function register_skin_deactivation_hook_function($code, $function)
    {
        $GLOBALS[ 'register_skin_deactivation_hook_function' . $code] = $function;
        $fn=create_function('$skin', ' call_user_func($GLOBALS["register_skin_deactivation_hook_function' . $code . '"]); delete_option("skin_is_activated_' . $code. '");');
        add_action( 'switch_s' , $fn );
    }
}

?>
-----
<?php

namespace Rector\Tests\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

final class SuperGlobal
{
    function register_skin_deactivation_hook_function($code, $function)
    {
        $GLOBALS[ 'register_skin_deactivation_hook_function' . $code] = $function;
        $fn=function ($skin) use ($code) {
            call_user_func($GLOBALS["register_skin_deactivation_hook_function{$code}"]);
            delete_option("skin_is_activated_{$code}");
        };
        add_action( 'switch_s' , $fn );
    }
}

?>
