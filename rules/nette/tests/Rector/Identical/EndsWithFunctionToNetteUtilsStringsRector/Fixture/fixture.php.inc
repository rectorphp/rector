<?php

namespace Rector\Nette\Tests\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector\Fixture;

class Fixture
{
    public function end($needle)
    {
        $content = 'Hi, my name is Tom';

        $yes = substr($content, -strlen($needle)) === $needle;
        $no = $needle !== substr($content, -strlen($needle));
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\Identical\EndsWithFunctionToNetteUtilsStringsRector\Fixture;

class Fixture
{
    public function end($needle)
    {
        $content = 'Hi, my name is Tom';

        $yes = \Nette\Utils\Strings::endsWith($content, $needle);
        $no = !\Nette\Utils\Strings::endsWith($content, $needle);
    }
}

?>
