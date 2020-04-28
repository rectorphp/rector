<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector\Fixture;

class PregMatchIdenticalOneWithMatches
{
    public function run($content)
    {
        $haveResults = preg_match('#\d#', $content, $matches) === 1;
        $this->matches = $matches;

        return $haveResults;
    }
}

?>
-----
<?php

namespace Rector\Nette\Tests\Rector\FuncCall\PregMatchFunctionToNetteUtilsStringsRector\Fixture;

class PregMatchIdenticalOneWithMatches
{
    public function run($content)
    {
        $haveResults = (bool) ($matches = \Nette\Utils\Strings::match($content, '#\d#'));
        $this->matches = $matches;

        return $haveResults;
    }
}

?>
