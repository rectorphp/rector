<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector\Fixture;

class PregMatchAll3Arguments
{
    public function run()
    {
        $content = 'Hi my name is Tom, Hi';

        preg_match_all('#Hi#', $content, $matches);
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector\Fixture;

class PregMatchAll3Arguments
{
    public function run()
    {
        $content = 'Hi my name is Tom, Hi';

        $matches = \Nette\Utils\Strings::matchAll($content, '#Hi#', PREG_SET_ORDER - 1);
    }
}

?>
