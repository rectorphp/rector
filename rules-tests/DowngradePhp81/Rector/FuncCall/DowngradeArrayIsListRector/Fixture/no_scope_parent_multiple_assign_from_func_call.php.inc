<?php

namespace Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeArrayIsListRector\Fixture;

final class MultipleAssign
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

        self::$instance = $loop = Factory::create();

        register_shutdown_function(function () {
            $test = array_is_list([]);
        });
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp81\Rector\FuncCall\DowngradeArrayIsListRector\Fixture;

final class MultipleAssign
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

        self::$instance = $loop = Factory::create();

        register_shutdown_function(function () {
            $arrayIsList = function (array $array) : bool {
                if (function_exists('array_is_list')) {
                    return array_is_list($array);
                }
                if ($array === []) {
                    return true;
                }
                $current_key = 0;
                foreach ($array as $key => $noop) {
                    if ($key !== $current_key) {
                        return false;
                    }
                    ++$current_key;
                }
                return true;
            };
            $test = $arrayIsList([]);
        });
    }
}

?>