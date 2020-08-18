<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture;

class InterfaceToClass implements \Countable
{
    public function run(\Countable $dateTime): \Countable
    {
    }

    public function count()
    {
    }
}

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\Fixture;

class InterfaceToClass implements \Countable
{
    public function run(\stdClass $dateTime): \stdClass
    {
    }

    public function count()
    {
    }
}

?>
