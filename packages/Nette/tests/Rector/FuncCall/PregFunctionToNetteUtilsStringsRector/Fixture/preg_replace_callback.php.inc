<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector\Fixture;

class PregReplaceCallback
{
    public function run()
    {
        $content = 'Hi my name is Tom';

        $newContent = preg_replace_callback('#Hi#', function ($value) {
            return 'Hello';
        }, $content);
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregFunctionToNetteUtilsStringsRector\Fixture;

class PregReplaceCallback
{
    public function run()
    {
        $content = 'Hi my name is Tom';

        $newContent = \Nette\Utils\Strings::replace($content, '#Hi#', function ($value) {
            return 'Hello';
        });
    }
}

?>
