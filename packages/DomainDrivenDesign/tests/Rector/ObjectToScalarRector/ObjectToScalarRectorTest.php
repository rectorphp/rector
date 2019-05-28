<?php declare(strict_types=1);

namespace Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarRector;

use Rector\DomainDrivenDesign\Rector\ObjectToScalar\ObjectToScalarRector;
use Rector\DomainDrivenDesign\Tests\Rector\ObjectToScalarRector\Source\SomeValueObject;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ObjectToScalarRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
            __DIR__ . '/Fixture/fixture4.php.inc',
        ]);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ObjectToScalarRector::class => [
                '$valueObjectsToSimpleTypes' => [
                    SomeValueObject::class => 'string',
                ],
            ],
        ];
    }
}
