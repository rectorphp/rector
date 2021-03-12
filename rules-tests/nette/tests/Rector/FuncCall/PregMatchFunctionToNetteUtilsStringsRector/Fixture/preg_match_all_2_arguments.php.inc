<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector\Fixture;

class PregMatchAll2Arguments
{
    public function run()
    {
        $content = 'Hi my name is Tom, Hi';

        $countOfMatches = preg_match_all('#Hi#', $content);

        if (preg_match_all('#Hi#', $content)) {
            // matched!
        }
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector\Fixture;

class PregMatchAll2Arguments
{
    public function run()
    {
        $content = 'Hi my name is Tom, Hi';

        $countOfMatches = count(\Nette\Utils\Strings::matchAll($content, '#Hi#'));

        if (\Nette\Utils\Strings::matchAll($content, '#Hi#')) {
            // matched!
        }
    }
}

?>
