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

    protected function getRectorClass(): string
    {
        return RenamePropertyRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            ClassWithProperties::class => [
                'oldProperty' => 'newProperty',
                'anotherOldProperty' => 'anotherNewProperty',
            ],
        ];
    }
}
