<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector\Fixture;

class PregReplaceWithString
{
    public function run()
    {
        $content = 'Hi my name is Tom';

        return (string) preg_replace('#[^0-9a-zA-Z]#', '_', $content);
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector\Fixture;

class PregReplaceWithString
{
    public function run()
    {
        $content = 'Hi my name is Tom';

        return (string) \Nette\Utils\Strings::replace($content, '#[^0-9a-zA-Z]#', '_');
    }
}

?>
