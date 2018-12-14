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

    protected function getRectorClass(): string
    {
        return ObjectToScalarRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [SomeValueObject::class => 'string'];
    }
}
