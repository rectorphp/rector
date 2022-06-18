<?php

namespace Rector\Core\Tests\Issues\DisableAutoImportDocBlock\Fixture;

final class SomeClass
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

