<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector\Fixture;

class PregSplit
{
    public function run()
    {
        $content = 'Hi my name is Tom';

        $splitted = preg_split('#Hi#', $content);
        $splitted = preg_split('#Hi#', $content, -1);
        $splitted = preg_split('#Hi#', $content, -1, PREG_SPLIT_NO_EMPTY);

        $splitted = preg_split('#Hi#', $content, 2);
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector\Fixture;

class PregSplit
{
    public function run()
    {
        $content = 'Hi my name is Tom';

        $splitted = \Nette\Utils\Strings::split($content, '#Hi#');
        $splitted = \Nette\Utils\Strings::split($content, '#Hi#');
        $splitted = \Nette\Utils\Strings::split($content, '#Hi#', PREG_SPLIT_NO_EMPTY);

        $splitted = preg_split('#Hi#', $content, 2);
    }
}

?>
