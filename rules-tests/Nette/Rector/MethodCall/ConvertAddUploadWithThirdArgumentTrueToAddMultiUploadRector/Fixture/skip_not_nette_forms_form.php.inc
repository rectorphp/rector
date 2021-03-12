<?php

namespace Rector\Tests\Nette\Rector\MethodCall\ConvertAddUploadWithThirdArgumentTrueToAddMultiUploadRector\Fixture;

use DateTime;

final class SkipSomeClass2
{
    public function run()
    {
        $d = new DateTime('now');
        $d->format('Y-m-d');
    }
}

?>
