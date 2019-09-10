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

            __DIR__ . '/Fixture/parent_constructor.php.inc',
            __DIR__ . '/Fixture/parent_constructor_with_own_inject.php.inc',
            __DIR__ . '/Fixture/parent_constructor_with_middle_class.php.inc',
        ]);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            AnnotatedPropertyInjectToConstructorInjectionRector::class => [
                '$annotation' => 'inject',
            ],
        ];
    }
}
