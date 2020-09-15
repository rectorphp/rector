<?php

declare(strict_types=1);

namespace Rector\Naming\Tests\Naming;

use Doctrine\Inflector\InflectorFactory;
use Iterator;
use Rector\Naming\Naming\PropertyNaming;
use Rector\Naming\RectorNamingInflector;
use Rector\Naming\ValueObject\ExpectedName;
use Rector\PHPStanStaticTypeMapper\Utils\TypeUnwrapper;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

final class PropertyNamingTest extends AbstractKernelTestCase
{
    /**
     * @dataProvider getExpectedNameFromMethodNameDataProvider
     */
    public function testGetExpectedNameFromMethodName(string $methodName, ?string $expectedPropertyName): void
    {
        $propertyNaming = new PropertyNaming(new TypeUnwrapper(), new RectorNamingInflector(
            InflectorFactory::create()->build()
        ));
        /** @var ExpectedName $actualPropertyName */
        $actualPropertyName = $propertyNaming->getExpectedNameFromMethodName($methodName);

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
