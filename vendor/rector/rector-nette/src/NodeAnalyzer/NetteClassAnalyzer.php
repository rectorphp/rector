<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
final class NetteClassAnalyzer
{
    /**
     * @var \Rector\NodeTypeResolver\NodeTypeResolver
     */
    private $nodeTypeResolver;
    public function __construct(\Rector\NodeTypeResolver\NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }
    public function isInComponent(\PhpParser\Node $node) : bool
    {
        if ($node instanceof \PhpParser\Node\Stmt\Class_) {
            $class = $node;
        } else {
            $class = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        }
        if (!$class instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        if (!$this->nodeTypeResolver->isObjectType($class, new \PHPStan\Type\ObjectType('Nette\\Application\\UI\\Control'))) {
            return \false;
        }
        if (!$this->nodeTypeResolver->isObjectType($class, new \PHPStan\Type\ObjectType('Nette\\Application\\UI\\Control'))) {
            return \false;
        }
        return !$this->nodeTypeResolver->isObjectType($class, new \PHPStan\Type\ObjectType('Nette\\Application\\UI\\Presenter'));
    }
}
