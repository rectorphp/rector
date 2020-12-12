<?php

namespace Rector\Nette\Tests\Rector\MethodCall\ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRector\Fixture;

use DateTime;

final class SkipNotNetteFormsForm
{
    public function run()
    {
        $d = new DateTime('now');
        $d->format('Y-m-d');
    }
}

?>
