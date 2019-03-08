<?php

namespace Rector\Php\Tests\Rector\FuncCall\PregReplaceEModifierRector\Fixture;

class Slashes
{
    public static function camelize($word) {
        return preg_replace('/(^|_)([a-z])/e', 'strtoupper("\\2")', $word);
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\PregReplaceEModifierRector\Fixture;

class Slashes
{
    public static function camelize($word) {
        return preg_replace_callback('/(^|_)([a-z])/', function ($matches) {
            return strtoupper($matches[2]);
        }, $word);
    }
}

?>
