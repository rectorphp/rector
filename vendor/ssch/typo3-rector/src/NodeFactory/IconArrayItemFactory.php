<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\NodeFactory;

use RectorPrefix20220606\PhpParser\Comment;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayItem;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\NodeFactory;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
final class IconArrayItemFactory
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NodeFactory
     */
    private $nodeFactory;
    public function __construct(NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param array<string, mixed> $iconConfiguration
     */
    public function create(array $iconConfiguration, string $iconIdentifier) : ArrayItem
    {
        $value = $this->nodeFactory->createArray($iconConfiguration);
        $key = new String_($iconIdentifier);
        // hack to make array item print on a new line
        $attributes = [AttributeKey::COMMENTS => [new Comment(\PHP_EOL)]];
        return new ArrayItem($value, $key, \false, $attributes);
    }
}
