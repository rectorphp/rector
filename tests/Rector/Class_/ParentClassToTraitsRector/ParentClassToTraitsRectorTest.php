<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Class_\ParentClassToTraitsRector;

use Rector\Rector\Class_\ParentClassToTraitsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\AnotherParentObject;
use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\ParentObject;
use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SecondTrait;
use Rector\Tests\Rector\Class_\ParentClassToTraitsRector\Source\SomeTrait;

final class ParentClassToTraitsRectorTest extends AbstractRectorTestCase
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

    protected function getRectorClass(): string
    {
        return ParentClassToTraitsRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            ParentObject::class => [SomeTrait::class],
            AnotherParentObject::class => [SomeTrait::class, SecondTrait::class],
        ];
    }
}
