<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector\Fixture;

class PregMatchAll
{
    public function run()
    {
        $content = 'Hi my name is Tom, Hi';

        $countOfMatches = preg_match_all('#Hi#', $content);

        if (preg_match_all('#Hi#', $content)) {
            // matched!
        }

        preg_match_all('#Hi#', $content, $matches);
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector\Fixture;

class PregMatchAll
{
    public function run()
    {
        $content = 'Hi my name is Tom, Hi';

        $countOfMatches = count(\Nette\Utils\Strings::matchAll($content, '#Hi#'));

        if (\Nette\Utils\Strings::matchAll($content, '#Hi#')) {
            // matched!
        }

        $matches = \Nette\Utils\Strings::matchAll($content, '#Hi#');
    }
}

?>
