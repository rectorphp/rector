<?php

declare(strict_types=1);

namespace Rector\Naming\Tests\Naming;

use Iterator;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Naming\Naming\PropertyNaming;
use Rector\Naming\ValueObject\ExpectedName;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;

final class PropertyNamingTest extends AbstractKernelTestCase
{
    /**
     * @var PropertyNaming
     */
    private $propertyNaming;

    protected function setUp(): void
    {
        $this->bootKernel(RectorKernel::class);
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
