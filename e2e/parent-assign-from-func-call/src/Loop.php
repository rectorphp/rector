<?php

final class Loop
{
    /**
     * @var LoopInterface
     */
    private static $instance;

    public static function get()
    {
        if (self::$instance instanceof LoopInterface) {
            return self::$instance;
        }

        register_shutdown_function(function () {
            $test = array_is_list([]);
        });
    }
}
