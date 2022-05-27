<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\NodeFactory;

use PhpParser\Comment;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Scalar\String_;
use Rector\Core\PhpParser\Node\NodeFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;
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
