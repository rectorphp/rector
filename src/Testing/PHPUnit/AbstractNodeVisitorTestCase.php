<?php

declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use Nette\Utils\FileSystem;
use PhpParser\Node;
use PhpParser\NodeDumper;
use Rector\HttpKernel\RectorKernel;
use Rector\PhpParser\BetterNodeDumper;
use Rector\PhpParser\Parser\Parser;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

/**
 * Class can be used to test node visitors
 * To update the fixture run phpunit as follows
 * $ UPDATE_FIXTURE=1 vendor/bin/phpunit
 */
abstract class AbstractNodeVisitorTestCase extends AbstractKernelTestCase
{
    /**
     * @var NodeDumper
     */
    protected $nodeDumper;

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
        parent::setUp();
        $this->bootKernelWithConfigs(RectorKernel::class, []);
        $this->fixtureSplitter = new FixtureSplitter($this->getTempPath());
        $this->parser = static::$container->get(Parser::class);

        $this->nodeDumper = new BetterNodeDumper();
        $this->nodeDumper->setFilterAttributes($this->getRelevantAttributes());
    }

    /**
     * @param Node[] $nodes
     */
    abstract protected function visitNodes(array $nodes): void;

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
        $smartFileInfo2 = new SmartFileInfo($expectedNodesFile);

        $nodes = $this->parser->parseFile($originalFile);

        $this->visitNodes($nodes);

        $dumpedNodes = $this->nodeDumper->dump($nodes);

        if (getenv('UPDATE_FIXTURE')) {
            FileSystem::write($file, FileSystem::read($originalFile) . "-----\n" . $dumpedNodes);
        } else {
            $this->assertSame(trim($smartFileInfo2->getContents()), $dumpedNodes, 'Caused by ' . $file);
        }
    }
}
