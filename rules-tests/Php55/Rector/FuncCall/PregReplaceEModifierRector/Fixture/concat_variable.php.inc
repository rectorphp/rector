<?php

namespace Rector\Tests\Php55\Rector\FuncCall\PregReplaceEModifierRector\Fixture;

class ConcatVariable
{
    public function run()
    {
        $test = 'become : ';
        $string  ='string';
        echo preg_replace("#([a-z]*)#e", "'".$test."' . strtoupper('\\1')", $string);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php55\Rector\FuncCall\PregReplaceEModifierRector\Fixture;

class ConcatVariable
{
    public function run()
    {
        $test = 'become : ';
        $string  ='string';
        echo preg_replace_callback('#([a-z]*)#', function ($matches) use ($test) {
            return $test . strtoupper($matches[1]);
        }, $string);
    }
}

?>
