<?php

declare(strict_types=1);

namespace Rector\Core\Testing\PHPUnit;

use Iterator;
use Nette\Utils\FileSystem;
use PhpParser\Node;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\PhpParser\Parser\Parser;
use Rector\Core\Testing\Dumper\AttributeFilteringNodeDumper;
use Rector\Core\Testing\StaticFixtureProvider;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Class can be used to test node visitors
 * To update the fixture run phpunit as follows
 * $ UPDATE_FIXTURE=1 vendor/bin/phpunit
 */
abstract class AbstractNodeVisitorTestCase extends AbstractKernelTestCase
{
    /**
     * @var AttributeFilteringNodeDumper
     */
    protected $attributeFilteringNodeDumper;

    /**
     * @var Parser
     */
    protected $parser;

    /**
     * @var FixtureSplitter
     */
    protected $fixtureSplitter;

    protected function setUp(): void
    {
        $this->bootKernelWithConfigs(RectorKernel::class, []);

        $this->fixtureSplitter = new FixtureSplitter($this->getTempPath());
        $this->parser = static::$container->get(Parser::class);

        $this->attributeFilteringNodeDumper = new AttributeFilteringNodeDumper($this->getRelevantAttributes());
    }

    /**
     * @param Node[] $nodes
     */
    abstract protected function traverseNodes(array $nodes): void;

    /**
     * @return string[]
     */
    protected function getRelevantAttributes(): array
    {
        return [];
    }

    protected function getTempPath(): string
    {
        return sys_get_temp_dir() . '/rector_temp_tests';
    }

    protected function doTestFile(string $file): void
    {
        $smartFileInfo = new SmartFileInfo($file);
        [$originalFile, $expectedNodesFile] = $this->fixtureSplitter->splitContentToOriginalFileAndExpectedFile(
            $smartFileInfo,
            true
        );

        $originalFileInfo = new SmartFileInfo($originalFile);
        $expectedNodesFileInfo = new SmartFileInfo($expectedNodesFile);

        $nodes = $this->parser->parseFileInfo($originalFileInfo);
        $this->traverseNodes($nodes);

        $dumpedNodes = $this->attributeFilteringNodeDumper->dump($nodes);

        if (getenv('UPDATE_FIXTURE')) {
            FileSystem::write($file, FileSystem::read($originalFile) . "-----\n" . $dumpedNodes);
        } else {
            $this->assertSame(trim($expectedNodesFileInfo->getContents()), $dumpedNodes, 'Caused by ' . $file);
        }
    }

    protected function yieldFilesFromDirectory(string $directory, string $suffix): Iterator
    {
        return StaticFixtureProvider::yieldFilesFromDirectory($directory, $suffix);
    }
}
