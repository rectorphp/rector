<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector;

use Rector\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class AnnotatedPropertyInjectToConstructorInjectionRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
            __DIR__ . '/Fixture/fixture4.php.inc',
            __DIR__ . '/Fixture/fixture5.php.inc',
            __DIR__ . '/Fixture/fixture6.php.inc',
            __DIR__ . '/Fixture/fixture7.php.inc',
            __DIR__ . '/Fixture/fixture8.php.inc',
            __DIR__ . '/Fixture/fixture9.php.inc',
            __DIR__ . '/Fixture/fixture10.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return AnnotatedPropertyInjectToConstructorInjectionRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return ['$annotation' => 'inject'];
    }
}
