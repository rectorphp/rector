<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\MethodCall\CascadeValidationFormBuilderRector;

use Rector\Symfony\Rector\MethodCall\CascadeValidationFormBuilderRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CascadeValidationFormBuilderRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    public function getRectorClass(): string
    {
        return CascadeValidationFormBuilderRector::class;
    }
}
