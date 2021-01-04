<?php

namespace Rector\Renaming\Tests\Rector\Name\RenameClassRector\FixtureAutoImportNames;

use Safe\DateTimeImmutable;

final class DemoFile
{
    private \DateTimeImmutable $date;

    public function getDate(): \DateTimeImmutable
    {
        $x = new \DateTimeImmutable();
        return $this->date;
    }
}

?>
