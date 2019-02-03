<?php declare(strict_types=1);

namespace Rector\Jms\Tests\Rector\Property\JmsInjectAnnotationRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class JmsInjectAnnotationRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
            __DIR__ . '/Fixture/fixture4.php.inc',
            __DIR__ . '/Fixture/fixture5.php.inc',
        ]);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yaml';
    }
}
