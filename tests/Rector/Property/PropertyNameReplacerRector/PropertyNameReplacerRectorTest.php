<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Property\PropertyNameReplacerRector;

use Rector\Rector\Property\PropertyNameReplacerRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class PropertyNameReplacerRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    protected function getRectorClass(): string
    {
        return PropertyNameReplacerRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return ['SomeClass' => [
            'oldProperty' => 'newProperty',
            'anotherOldProperty' => 'anotherNewProperty',
        ]];
    }
}
