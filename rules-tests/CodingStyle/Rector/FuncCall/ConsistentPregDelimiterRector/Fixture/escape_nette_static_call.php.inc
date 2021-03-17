<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector\Fixture;

use Nette\Utils\Strings;

class EscapeNetteStaticCall
{
    public function run($value)
    {
        Strings::match($value, '/value/');
    }
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\FuncCall\ConsistentPregDelimiterRector\Fixture;

use Nette\Utils\Strings;

class EscapeNetteStaticCall
{
    public function run($value)
    {
        Strings::match($value, '#value#');
    }
}

?>
