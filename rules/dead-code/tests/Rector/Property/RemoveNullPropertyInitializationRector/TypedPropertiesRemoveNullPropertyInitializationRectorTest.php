<?php

declare(strict_types=1);

namespace Rector\DeadCode\Tests\Rector\Property\RemoveNullPropertyInitializationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\DeadCode\Rector\Property\RemoveNullPropertyInitializationRector;

final class TypedPropertiesRemoveNullPropertyInitializationRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureTypedProperties');
    }

    protected function getRectorClass(): string
    {
        return RemoveNullPropertyInitializationRector::class;
    }

    protected function getPhpVersion(): string
    {
        return PhpVersionFeature::TYPED_PROPERTIES;
    }
}
