<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector\Fixture;

class PregMatch2Arguments
{
    public function run()
    {
        $content = 'Hi my name is Tom';

        $isIt = preg_match('#Hi#', $content);

        if (preg_match('#Hi#', $content)) {
            // matched!
        }
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector\Fixture;

class PregMatch2Arguments
{
    public function run()
    {
        $content = 'Hi my name is Tom';

        $isIt = preg_match('#Hi#', $content);

        if (\Nette\Utils\Strings::match($content, '#Hi#')) {
            // matched!
        }
    }
}

?>
