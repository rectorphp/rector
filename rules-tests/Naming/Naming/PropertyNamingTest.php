<?php

declare(strict_types=1);

namespace Rector\Tests\Naming\Naming;

use Iterator;
use PHPStan\Type\ObjectType;
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
        /** @var ExpectedName $expectedName */
        $expectedName = $this->propertyNaming->getExpectedNameFromMethodName($methodName);

        if ($expectedPropertyName === null) {
            $this->assertNull($expectedName);
        } else {
            $this->assertSame($expectedPropertyName, $expectedName->getSingularized());
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

    /**
     * @dataProvider provideDataPropertyName()
     */
    public function testPropertyName(string $objectName, string $expectedVariableName): void
    {
        $variableName = $this->propertyNaming->fqnToVariableName(new ObjectType($objectName));
        $this->assertSame($expectedVariableName, $variableName);
    }

    /**
     * @return Iterator<string, string>
     */
    public function provideDataPropertyName(): Iterator
    {
        yield ['SomeVariable', 'someVariable'];
        yield ['IControl', 'control'];
        yield ['AbstractValueClass', 'valueClass'];
        yield ['App\AbstractValueClass', 'valueClass'];
        yield ['Twig_Extension', 'twigExtension'];
        yield ['NodeVisitorAbstract', 'nodeVisitor'];
        yield ['AbstractNodeVisitor', 'nodeVisitor'];
        yield ['Twig_ExtensionInterface', 'twigExtension'];
    }
}
