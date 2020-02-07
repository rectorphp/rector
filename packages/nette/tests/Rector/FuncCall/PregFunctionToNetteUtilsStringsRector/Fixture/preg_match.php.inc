<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector\Fixture;

class SomeClass
{
    public function run()
    {
        $content = 'Hi my name is Tom';

        $isIt = preg_match('#Hi#', $content);

        if (preg_match('#Hi#', $content)) {
            // matched!
        }

        preg_match('#Hi#', $content, $matches);
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector\Fixture;

class SomeClass
{
    public function run()
    {
        $content = 'Hi my name is Tom';

        $isIt = preg_match('#Hi#', $content);

        if (\Nette\Utils\Strings::match($content, '#Hi#')) {
            // matched!
        }

        $matches = \Nette\Utils\Strings::match($content, '#Hi#');
    }
}

?>
