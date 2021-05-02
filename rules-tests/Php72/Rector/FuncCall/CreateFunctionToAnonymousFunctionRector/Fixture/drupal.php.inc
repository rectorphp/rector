<?php

namespace Rector\Tests\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

class Drupal
{
    public function run()
    {
        $replace = create_function(
            '$m',
            '$r="{$m[1]}ecursion_features";
            return \'s:\'.strlen($r.$m[2]).\':"\'.$r.$m[2].\'";\';'
        );

        $replace = create_function(
            '$matches',
            'return $matches[1]?"-":"";'
        );
    }
}

?>
-----
<?php

namespace Rector\Tests\Php72\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

class Drupal
{
    public function run()
    {
        $replace = function ($m) {
<<<<<<< HEAD
            $r = "{$m[1]}ecursion_features";
=======
            $r = "\x00{$m[1]}ecursion_features";
>>>>>>> df2aaaa3c (add preload to test case)
            return 's:' . strlen($r . $m[2]) . ':"' . $r . $m[2] . '";';
        };

        $replace = function ($matches) {
            return $matches[1] ? "-" : "";
        };
    }
}

?>
