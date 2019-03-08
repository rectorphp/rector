<?php

namespace Rector\Php\Tests\Rector\FuncCall\PregReplaceEModifierRector\Fixture;

class SomeClass
{
    public function run()
    {
        $comment = preg_replace('~\b(\w)(\w+)~e', '"$1".strtolower("$2")', $comment);
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\PregReplaceEModifierRector\Fixture;

class SomeClass
{
    public function run()
    {
        $comment = preg_replace_callback('~\b(\w)(\w+)~', function ($matches) {
            return $matches[1] . strtolower($matches[2]);
        }, $comment);
    }
}

?>
