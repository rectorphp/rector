<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

define ('SOME_DEFINE', ['a', 'p']);

class Consts
{
    const SOME_CONST = ['a', 'p'];

    public function run($string)
    {
        // Consts: if array, change to string
        strip_tags($string, SOME_DEFINE);
        strip_tags($string, self::SOME_CONST);
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

define ('SOME_DEFINE', ['a', 'p']);

class Consts
{
    const SOME_CONST = ['a', 'p'];

    public function run($string)
    {
        // Consts: if array, change to string
        strip_tags($string, SOME_DEFINE !== null && is_array(SOME_DEFINE) ? '<' . implode('><', SOME_DEFINE) . '>' : SOME_DEFINE);
        strip_tags($string, self::SOME_CONST !== null && is_array(self::SOME_CONST) ? '<' . implode('><', self::SOME_CONST) . '>' : self::SOME_CONST);
    }
}

?>
