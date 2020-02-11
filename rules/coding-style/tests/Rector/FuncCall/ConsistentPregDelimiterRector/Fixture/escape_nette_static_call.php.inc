<?php

namespace Rector\CodingStyle\Tests\Rector\FuncCall\ConsistentPregDelimiterRector\Fixture;

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

namespace Rector\CodingStyle\Tests\Rector\FuncCall\ConsistentPregDelimiterRector\Fixture;

use Nette\Utils\Strings;

class EscapeNetteStaticCall
{
    public function run($value)
    {
        Strings::match($value, '#value#');
    }
}

?>
