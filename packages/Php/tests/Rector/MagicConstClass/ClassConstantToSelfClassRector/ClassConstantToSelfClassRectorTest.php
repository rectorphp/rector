<?php declare(strict_types=1);

namespace Rector\Php\Tests\Rector\MagicConstClass\ClassConstantToSelfClassRector;

use Rector\Php\Rector\MagicConstClass\ClassConstantToSelfClassRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ClassConstantToSelfClassRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return ClassConstantToSelfClassRector::class;
    }
}
