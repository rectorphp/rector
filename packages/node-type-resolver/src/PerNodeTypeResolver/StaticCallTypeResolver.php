<?php

declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Type\Type;
use PHPStan\Type\TypeUtils;
use Rector\Core\PhpParser\Node\Resolver\NameResolver;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class StaticCallTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(NameResolver $nameResolver)
    {
        $this->nameResolver = $nameResolver;
    }

    /**
     * @required
     */
    public function autowirePropertyTypeResolver(NodeTypeResolver $nodeTypeResolver): void
    {
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @return string[]
     */
    public function getNodeClasses(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function resolve(Node $node): Type
    {
        $classType = $this->nodeTypeResolver->resolve($node->class);
        $methodName = $this->nameResolver->getName($node->name);

        // no specific method found, return class types, e.g. <ClassType>::$method()
        if (! is_string($methodName)) {
            return $classType;
        }

        $classNames = TypeUtils::getDirectClassNames($classType);
        foreach ($classNames as $className) {
            if (! method_exists($className, $methodName)) {
                continue;
            }

            /** @var Scope|null $nodeScope */
            $nodeScope = $node->getAttribute(AttributeKey::SCOPE);
            if ($nodeScope === null) {
                return $classType;
            }

            return $nodeScope->getType($node);
        }

        return $classType;
    }
}
