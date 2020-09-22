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

    public function __construct(SmartFinder $smartFinder, BetterStandardPrinter $betterStandardPrinter)
    {
        $this->smartFinder = $smartFinder;
        $this->betterStandardPrinter = $betterStandardPrinter;
    }

    /**
     * @return array<string, NodeCodeSample[]>
     */
    public function provide(): array
    {
        $snippetFileInfos = $this->smartFinder->find([__DIR__ . '/../snippet'], '*.php.inc');

        $nodeCodeSamplesByNodeClass = [];

        foreach ($snippetFileInfos as $fileInfo) {
            $node = include $fileInfo->getRealPath();
            $this->ensureReturnsNodeObject($node, $fileInfo);

            $nodeClass = get_class($node);

            $printedContent = $this->betterStandardPrinter->print($node);
            $nodeCodeSamplesByNodeClass[$nodeClass][] = new NodeCodeSample(
                $fileInfo->getContents(),
                $printedContent
            );
        }

        ksort($nodeCodeSamplesByNodeClass);

        return $nodeCodeSamplesByNodeClass;
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
