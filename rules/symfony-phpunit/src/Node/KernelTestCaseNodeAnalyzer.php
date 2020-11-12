<?php

declare(strict_types=1);

namespace Rector\SymfonyPHPUnit\Node;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;

final class KernelTestCaseNodeAnalyzer
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NodeNameResolver $nodeNameResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function isOnContainerGetMethodCall(Node $node): bool
    {
        return $this->isSelfContainerGetMethodCall($node);
    }

    /**
     * Is inside setUp() class method
     */
    public function isSetUpOrEmptyMethod(Node $node): bool
    {
        $methodName = $node->getAttribute(AttributeKey::METHOD_NAME);

        return $methodName === MethodName::SET_UP || $methodName === null;
    }

    /**
     * Matches:
     * self::$container->get()
     */
    private function isSelfContainerGetMethodCall(Node $node): bool
    {
        if (! $node instanceof MethodCall) {
            return false;
        }

        if (! $this->nodeNameResolver->isName($node->name, 'get')) {
            return false;
        }

        return $this->nodeTypeResolver->isObjectType(
            $node->var,
            'Symfony\Component\DependencyInjection\ContainerInterface'
        );
    }
}
