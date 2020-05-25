<?php

declare(strict_types=1);

namespace Rector\PhpAttribute\Printer;

use PhpParser\Comment;
use PhpParser\Node\Stmt\Nop;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpAttribute\Collector\PlaceholderToValueCollector;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;

final class PlaceholderNodeFactory
{
    /**
     * @var int
     */
    private $phpAttributePlaceholderCounter = 1;

    /**
     * @var PlaceholderToValueCollector
     */
    private $placeholderToValueCollector;

    public function __construct(PlaceholderToValueCollector $placeholderToValueCollector)
    {
        $this->placeholderToValueCollector = $placeholderToValueCollector;
    }

    /**
     * @param PhpAttributableTagNodeInterface[] $phpAttributableTagNodes
     */
    public function create(array $phpAttributableTagNodes): Nop
    {
        // 1. change attributable tags to string
        $phpAttributesString = $this->createPhpAttributesString($phpAttributableTagNodes);

        // 2. create placeholder node
        $placeholderNop = new Nop();
        $placeholderName = $this->createPlaceholderName();
        $placeholderNop->setAttribute(AttributeKey::COMMENTS, [new Comment($placeholderName)]);

        // 3. store key/value placeholder
        $this->placeholderToValueCollector->add($placeholderName, $phpAttributesString);
        ++$this->phpAttributePlaceholderCounter;

        return $placeholderNop;
    }

    /**
     * @param PhpAttributableTagNodeInterface[] $phpAttributableTagNodes
     */
    private function createPhpAttributesString(array $phpAttributableTagNodes): string
    {
        $phpAttributesStrings = [];
        foreach ($phpAttributableTagNodes as $phpAttributableTagNode) {
            $phpAttributesStrings[] = $phpAttributableTagNode->toAttributeString();
        }

        return implode(PHP_EOL, $phpAttributesStrings);
    }

    private function createPlaceholderName(): string
    {
        return '__PHP_ATTRIBUTE_PLACEHOLDER_' . $this->phpAttributePlaceholderCounter . '__';
    }
}
