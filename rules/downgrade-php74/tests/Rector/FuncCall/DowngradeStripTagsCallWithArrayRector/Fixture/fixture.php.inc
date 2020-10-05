<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

define ('SOME_DEFINE', ['a', 'p']);

class FixtureClass
{
    const SOME_CONST = ['a', 'p'];

    public function run($string)
    {
        // Arrays: change to string
        strip_tags($string, ['a', 'p']);

        // Variables: if array, change to string
        $tags = ['a', 'p'];
        strip_tags($string, $tags);

        // Consts: if array, change to string
        strip_tags($string, SOME_DEFINE);
        strip_tags($string, self::SOME_CONST);

        // Function/method call: if array, change to string
        strip_tags($string, getTags());
        strip_tags($string, $this->getTags());
    }
}

?>
-----
<?php

namespace Rector\DowngradePhp74\Tests\Rector\FuncCall\DowngradeStripTagsCallWithArrayRector\Fixture;

define ('SOME_DEFINE', ['a', 'p']);

class FixtureClass
{
    const SOME_CONST = ['a', 'p'];

    public function run($string)
    {
        // Arrays: change to string
        strip_tags($string, '<' . implode('><', ['a', 'p']) . '>');

        // Variables: if array, change to string
        $tags = ['a', 'p'];
        strip_tags($string, is_array($tags) ? '<' . implode('><', $tags) . '>' : $tags);

        // Consts: if array, change to string
        strip_tags($string, is_array(SOME_DEFINE) ? '<' . implode('><', SOME_DEFINE) . '>' : SOME_DEFINE);
        strip_tags($string, is_array(self::SOME_CONST) ? '<' . implode('><', self::SOME_CONST) . '>' : self::SOME_CONST);

        // Function/method call: if array, change to string
        strip_tags($string, is_array($tags = getTags()) ? '<' . implode('><', $tags) . '>' : $tags);
        strip_tags($string, is_array($tags = $this->getTags()) ? '<' . implode('><', $tags) . '>' : $tags);
    }
}

?>
