<?php

namespace Rector\Php72\Tests\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

class Drupal
{
    public function run()
    {
        $replace = create_function(
            '$m',
            '$r="\x00{$m[1]}ecursion_features";
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

namespace Rector\Php72\Tests\Rector\FuncCall\CreateFunctionToAnonymousFunctionRector\Fixture;

class Drupal
{
    public function run()
    {
        $replace = function ($m) {
            $r = "\0{$m[1]}ecursion_features";
            return 's:' . strlen($r . $m[2]) . ':"' . $r . $m[2] . '";';
        };

        $replace = function ($matches) {
            return $matches[1] ? "-" : "";
        };
    }
}

?>
