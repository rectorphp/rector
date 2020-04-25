<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector\Fixture;

class PregMatch3Arguments
{
    public function run()
    {
        $content = 'Hi my name is Tom';

        preg_match('#Hi#', $content, $matches);
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector\Fixture;

class PregMatch3Arguments
{
    public function run()
    {
        $content = 'Hi my name is Tom';

        $matches = \Nette\Utils\Strings::match($content, '#Hi#');
    }
}

?>
