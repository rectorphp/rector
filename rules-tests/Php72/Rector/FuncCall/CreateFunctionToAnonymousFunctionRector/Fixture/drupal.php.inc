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
            $r = "{$m[1]}ecursion_features";
            return 's:' . strlen($r . $m[2]) . ':"' . $r . $m[2] . '";';
        };

        $replace = function ($matches) {
            return $matches[1] ? "-" : "";
        };
    }
}

?>
