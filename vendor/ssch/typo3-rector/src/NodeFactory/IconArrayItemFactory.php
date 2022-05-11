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
    public function __construct(\Rector\Core\PhpParser\Node\NodeFactory $nodeFactory)
    {
        $this->nodeFactory = $nodeFactory;
    }
    /**
     * @param array<string, mixed> $iconConfiguration
     */
    public function create(array $iconConfiguration, string $iconIdentifier) : \PhpParser\Node\Expr\ArrayItem
    {
        $value = $this->nodeFactory->createArray($iconConfiguration);
        $key = new \PhpParser\Node\Scalar\String_($iconIdentifier);
        // hack to make array item print on a new line
        $attributes = [\Rector\NodeTypeResolver\Node\AttributeKey::COMMENTS => [new \PhpParser\Comment(\PHP_EOL)]];
        return new \PhpParser\Node\Expr\ArrayItem($value, $key, \false, $attributes);
    }
}
