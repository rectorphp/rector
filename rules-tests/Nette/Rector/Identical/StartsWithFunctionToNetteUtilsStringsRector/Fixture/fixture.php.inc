<?php

namespace Rector\Nette\Tests\Rector\Identical\StartsWithFunctionToNetteUtilsStringsRector\Fixture;

class Fixture
{
    public function start($needle)
    {
        $content = 'Hi, my name is Tom';

        $yes = substr($content, 0, strlen($needle)) === $needle;
        $no = $needle !== substr($content, 0, strlen($needle));
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\Identical\StartsWithFunctionToNetteUtilsStringsRector\Fixture;

class Fixture
{
    public function start($needle)
    {
        $content = 'Hi, my name is Tom';

        $yes = \Nette\Utils\Strings::startsWith($content, $needle);
        $no = !\Nette\Utils\Strings::startsWith($content, $needle);
    }
}

?>
