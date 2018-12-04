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
            __DIR__ . '/Wrong/wrong.php.inc',
            __DIR__ . '/Wrong/wrong2.php.inc',
            __DIR__ . '/Wrong/wrong3.php.inc',
            __DIR__ . '/Wrong/wrong4.php.inc',
            __DIR__ . '/Wrong/wrong5.php.inc',
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
