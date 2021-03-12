<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector\Fixture;

class PregMatchIdenticalOne
{
    public function run($content)
    {
        return preg_match('#\d#', $content, $matches) === 1;
    }

    public function go($content)
    {
        return 1 === preg_match('#\d#', $content);
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector\Fixture;

class PregMatchIdenticalOne
{
    public function run($content)
    {
        return (bool) \Nette\Utils\Strings::match($content, '#\d#');
    }

    public function go($content)
    {
        return (bool) \Nette\Utils\Strings::match($content, '#\d#');
    }
}

?>
