<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Architecture\Factory\NewObjectToFactoryCreateRector;

use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class NewObjectToFactoryCreateRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/fixture2.php.inc']);
    }

    protected function provideConfig(): string
    {
        return __DIR__ . '/config.yml';
    }
}
