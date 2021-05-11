<?php

declare(strict_types=1);

namespace Rector\Tests\Naming\Naming;

use Iterator;
use Rector\Naming\Naming\PropertyNaming;
use Rector\Naming\ValueObject\ExpectedName;
use Rector\Testing\PHPUnit\AbstractTestCase;

final class PropertyNamingTest extends AbstractTestCase
{
    private PropertyNaming $propertyNaming;

    protected function setUp(): void
    {
        $this->boot();
        $this->propertyNaming = $this->getService(PropertyNaming::class);
    }

    /**
     * @dataProvider getExpectedNameFromMethodNameDataProvider
     */
    public function testGetExpectedNameFromMethodName(string $methodName, ?string $expectedPropertyName): void
    {
        /** @var ExpectedName $actualPropertyName */
        $actualPropertyName = $this->propertyNaming->getExpectedNameFromMethodName($methodName);

        if ($expectedPropertyName === null) {
            $this->assertNull($actualPropertyName);
        } else {
            $this->assertSame($expectedPropertyName, $actualPropertyName->getSingularized());
        }
    }

    /**
     * @return Iterator<mixed>
     */
    public function getExpectedNameFromMethodNameDataProvider(): Iterator
    {
        yield ['getMethods', 'method'];
        yield ['getUsedTraits', 'usedTrait'];
        yield ['getPackagesData', 'packageData'];
        yield ['getPackagesInfo', 'packageInfo'];
        yield ['getAnythingElseData', 'anythingElseData'];
        yield ['getAnythingElseInfo', 'anythingElseInfo'];
        yield ['getSpaceshipsInfo', 'spaceshipInfo'];
        yield ['resolveDependencies', null];
    }
}
