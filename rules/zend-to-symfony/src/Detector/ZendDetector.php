<?php

declare(strict_types=1);

namespace Rector\ZendToSymfony\Detector;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\ZendToSymfony\ValueObject\ZendClass;

final class ZendDetector
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeTypeResolver $nodeTypeResolver, NodeNameResolver $nodeNameResolver)
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    public function isInZendController(Node $node): bool
    {
        if (! $node instanceof Class_) {
            /** @var Class_|null $node */
            $node = $node->getAttribute(AttributeKey::CLASS_NODE);
        }

        if ($node === null) {
            return false;
        }

        return $this->nodeTypeResolver->isObjectType($node, ZendClass::CONTROLLER_ACTION);
    }

    public function isZendActionMethod(ClassMethod $classMethod): bool
    {
        if (! $classMethod->isPublic()) {
            return false;
        }

        return $this->nodeNameResolver->isName($classMethod, '*Action');
    }
}
