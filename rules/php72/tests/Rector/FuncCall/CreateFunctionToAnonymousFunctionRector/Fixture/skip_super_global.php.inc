<?php

namespace Rector\Php72\Tests\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

final class SkipSuperGlobal
{
   function register_skin_deactivation_hook_function($code, $function)
   {
        $GLOBALS[ 'register_skin_deactivation_hook_function' . $code] = $function;
        $fn=create_function('$skin', ' call_user_func($GLOBALS["register_skin_deactivation_hook_function' . $code . '"]); delete_option("skin_is_activated_' . $code. '");');
        add_action( 'switch_s' , $fn );
    }
}