<?php

namespace Rector\Php55\Tests\Rector\FuncCall\PregReplaceEModifierRector\Fixture;

class CallFunction
{
    public function run()
    {
        $result = preg_replace(
            "/\{([<>])([a-zA-Z0-9_]*)(\?{0,1})([a-zA-Z0-9_]*)\}(.*)\{\\1\/\\2\}/iseU",
            "CallFunction('\\1','\\2','\\3','\\4','\\5')",
            $result
        );
    }
}

?>
-----
<?php

namespace Rector\Php55\Tests\Rector\FuncCall\PregReplaceEModifierRector\Fixture;

class CallFunction
{
    public function run()
    {
        $result = preg_replace_callback(
            '/\{([<>])([a-zA-Z0-9_]*)(\?{0,1})([a-zA-Z0-9_]*)\}(.*)\{\1\/\2\}/isU',
            function ($matches) {
                return CallFunction($matches[1], $matches[2], $matches[3], $matches[4], $matches[5]);
            },
            $result
        );
    }
}

?>
