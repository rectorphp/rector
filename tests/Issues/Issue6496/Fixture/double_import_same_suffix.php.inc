<?php

namespace Rector\Core\Tests\Issues\Issue6496\Fixture;

final class DoubleImportSameSuffix
{
    /**
     * @throws \PHPUnit\Framework\InvalidArgumentException
     * @throws \InvalidArgumentException
     */
    public function run()
    {
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue6496\Fixture;

use PHPUnit\Framework\InvalidArgumentException;
final class DoubleImportSameSuffix
{
    /**
     * @throws InvalidArgumentException
     * @throws \InvalidArgumentException
     */
    public function run()
    {
    }
}

?>
