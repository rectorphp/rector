<?php

declare(strict_types=1);

namespace Rector\Core\Testing\PHPUnit;

use PhpParser\Node;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\PhpParser\Parser\Parser;
use Rector\Core\Testing\Node\NodeAttributeExtractor;
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
     * @var NodeAttributeExtractor
     */
    protected $nodeAttributeExtractor;

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

        $this->parser = static::$container->get(Parser::class);
        $this->nodeAttributeExtractor = static::$container->get(NodeAttributeExtractor::class);
    }

    /**
     * @param Node[] $nodes
     */
    abstract protected function traverseNodes(array $nodes): void;

    protected function parseFileToAttribute(string $file, string $relevantAttribute): array
    {
        $fileInfo = new SmartFileInfo($file);
        $nodes = $this->parser->parseFileInfo($fileInfo);

        $this->traverseNodes($nodes);

        return $this->nodeAttributeExtractor->extract($nodes, $relevantAttribute);
    }
}
