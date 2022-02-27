<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use DateTime;

final class DoNotReplaceInterfaceCall
{
    public function run($dateTime)
    {
        if (! $dateTime instanceof DateTime) {
            $dateTime = DateTime::createFromFormat('Y-m-d', date('Y-m-d'));
        }

        return $dateTime;
    }
}

?>
-----
<?php

namespace Rector\Tests\Renaming\Rector\Name\RenameClassRector\Fixture;

use DateTime;

final class DoNotReplaceInterfaceCall
{
    public function run($dateTime)
    {
        if (! $dateTime instanceof \DateTimeInterface) {
            $dateTime = DateTime::createFromFormat('Y-m-d', date('Y-m-d'));
        }

        return $dateTime;
    }
}

?>
