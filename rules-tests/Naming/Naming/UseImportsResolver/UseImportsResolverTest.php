<?php

declare(strict_types=1);

namespace Rector\Tests\Naming\Naming\UseImportsResolver;

use Iterator;
use PhpParser\Node\Stmt\Property;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\Naming\Naming\UseImportsResolver;
use Rector\Testing\PHPUnit\AbstractTestCase;
use Rector\Testing\TestingParser\TestingParser;
use Rector\Tests\Naming\Naming\UseImportsResolver\Source\FirstClass;
use Rector\Tests\Naming\Naming\UseImportsResolver\Source\SecondClass;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\SmartFileSystem\SmartFileInfo;

final class UseImportsResolverTest extends AbstractTestCase
{
    private UseImportsResolver $useImportsResolver;

    private TestingParser $testingParser;

    private BetterNodeFinder $nodeFinder;

    protected function setUp(): void
    {
        $this->boot();
        $this->useImportsResolver = $this->getService(UseImportsResolver::class);
        $this->testingParser = $this->getService(TestingParser::class);
        $this->nodeFinder = $this->getService(BetterNodeFinder::class);
    }

    /**
     * @dataProvider provideData()
     */
    public function testUsesFromProperty(SmartFileInfo $file): void
    {
        $nodes = $this->testingParser->parseFileToDecoratedNodes($file->getRelativeFilePath());

        $firstProperty = $this->nodeFinder->findFirstInstanceOf($nodes, Property::class);
        $resolvedUses = $this->useImportsResolver->resolveForNode($firstProperty);

        $stringUses = [];

        foreach ($resolvedUses as $resolvedUse) {
            foreach ($resolvedUse->uses as $useUse) {
                $stringUses[] = $useUse->name->tostring();
            }
        }

        $this->assertContains(FirstClass::class, $stringUses);
        $this->assertContains(SecondClass::class, $stringUses);
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    public function provideData(): Iterator
    {
        $directory = __DIR__ . '/Fixture';
        return StaticFixtureFinder::yieldDirectoryExclusively($directory);
    }
}
