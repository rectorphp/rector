<?php

declare(strict_types=1);

namespace Rector\Utils\NodeDocumentationGenerator;

use PhpParser\Node;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Utils\NodeDocumentationGenerator\ValueObject\NodeCodeSample;
use Symplify\SmartFileSystem\Finder\SmartFinder;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NodeCodeSampleProvider
{
    /**
     * @var SmartFinder
     */
    private $smartFinder;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var array<string, NodeCodeSample[]>
     */
    private $nodeCodeSamplesByNodeClass = [];

    public function __construct(SmartFinder $smartFinder, BetterStandardPrinter $betterStandardPrinter)
    {
        $this->smartFinder = $smartFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    /**
     * @return class-string[]
     */
    public function getNodeClasses(): array
    {
        return array_keys($this->provide());
    }

    /**
     * @return array<string, NodeCodeSample[]>
     */
    public function provide(): array
    {
        if ($this->nodeCodeSamplesByNodeClass !== []) {
            return $this->nodeCodeSamplesByNodeClass;
        }

        $snippetFileInfos = $this->smartFinder->find([__DIR__ . '/../snippet'], '*.php.inc');

        foreach ($snippetFileInfos as $fileInfo) {
            $node = include $fileInfo->getRealPath();
            $this->ensureReturnsNodeObject($node, $fileInfo);

            $nodeClass = get_class($node);

            $printedContent = $this->betterStandardPrinter->print($node);
            $this->nodeCodeSamplesByNodeClass[$nodeClass][] = new NodeCodeSample(
                $fileInfo->getContents(),
                $printedContent
            );
        }

        ksort($this->nodeCodeSamplesByNodeClass);

        return $this->nodeCodeSamplesByNodeClass;
    }

    /**
     * @param mixed $node
     */
    private function ensureReturnsNodeObject($node, SmartFileInfo $fileInfo): void
    {
        if ($node instanceof Node) {
            return;
        }

        $message = sprintf('Snippet "%s" must return a node object', $fileInfo->getPathname());
        throw new ShouldNotHappenException($message);
    }
}
