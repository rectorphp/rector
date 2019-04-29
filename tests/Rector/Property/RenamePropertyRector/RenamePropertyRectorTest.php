<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Property\RenamePropertyRector;

use Rector\Rector\Property\RenamePropertyRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Property\RenamePropertyRector\Source\ClassWithProperties;

final class RenamePropertyRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc']);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [RenamePropertyRector::class => [
            '$oldToNewPropertyByTypes' => [
                ClassWithProperties::class => [
                    'oldProperty' => 'newProperty',
                    'anotherOldProperty' => 'anotherNewProperty',
                ],
            ],
        ]];
    }
}
