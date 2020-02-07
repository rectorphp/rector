<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector\Fixture;

class PregSplitWithSubpattern
{
    public function run()
    {
        $content = 'Hi my name is Tom';

        $pieces = preg_split('#\s#', $content);

        $pieces = preg_split('#(\s)#', $content);
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector\Fixture;

class PregSplitWithSubpattern
{
    public function run()
    {
        $content = 'Hi my name is Tom';

        $pieces = \Nette\Utils\Strings::split($content, '#\s#');

        $pieces = \Nette\Utils\Strings::split($content, '#(\s)#', 0 & ~PREG_SPLIT_DELIM_CAPTURE);
    }
}

?>
