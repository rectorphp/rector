<?php

namespace Rector\Php55\Tests\Rector\FuncCall\PregReplaceEModifierRector\Fixture;

class Slashes
{
    public static function camelize($word)
    {
        return preg_replace('#a#e', "strtoupper('\\0___');", $word);
    }
}

?>
-----
<?php

namespace Rector\Php55\Tests\Rector\FuncCall\PregReplaceEModifierRector\Fixture;

class Slashes
{
    public static function camelize($word)
    {
        return preg_replace_callback('#a#', function ($matches) {
            return strtoupper($matches[0]);
        }, $word);
    }
}

?>
