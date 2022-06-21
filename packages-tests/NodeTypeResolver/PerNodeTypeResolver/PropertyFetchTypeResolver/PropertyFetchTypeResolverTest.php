<?php

declare(strict_types=1);

namespace Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\PropertyFetchTypeResolver;

use Iterator;
use PhpParser\Node\Expr\PropertyFetch;
use PHPStan\Type\Type;
use PHPStan\Type\VerbosityLevel;
use Rector\Tests\NodeTypeResolver\PerNodeTypeResolver\AbstractNodeTypeResolverTest;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PropertyFetchTypeResolverTest extends AbstractNodeTypeResolverTest
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $smartFileInfo): void
    {
        $this->doTestFileInfo($smartFileInfo);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        return StaticFixtureFinder::yieldDirectoryExclusively(__DIR__ . '/Fixture');
    }

    private function doTestFileInfo(SmartFileInfo $smartFileInfo): void
    {
        $inputFileInfoAndExpectedFileInfo = StaticFixtureSplitter::splitFileInfoToLocalInputAndExpectedFileInfos(
            $smartFileInfo
        );
        $inputFileInfo = $inputFileInfoAndExpectedFileInfo->getInputFileInfo();
        $expectedFileInfo = $inputFileInfoAndExpectedFileInfo->getExpectedFileInfo();

        $propertyFetchNodes = $this->getNodesForFileOfType($inputFileInfo->getRealPath(), PropertyFetch::class);
        $resolvedType = $this->nodeTypeResolver->getType($propertyFetchNodes[0]);

        $expectedType = include $expectedFileInfo->getRealPath();

        $expectedTypeAsString = $this->getStringFromType($expectedType);
        $resolvedTypeAsString = $this->getStringFromType($resolvedType);

        $this->assertSame($expectedTypeAsString, $resolvedTypeAsString);
    }

    private function getStringFromType(Type $type): string
    {
        return $type->describe(VerbosityLevel::precise());
    }
}
