<?php

declare(strict_types=1);

namespace Rector\SymfonyPHPUnit\Node;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use Rector\Core\PhpParser\Node\Resolver\NameResolver;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class KernelTestCaseNodeAnalyzer
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(NameResolver $nameResolver, NodeTypeResolver $nodeTypeResolver)
    {
        $this->nameResolver = $nameResolver;
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

        return $methodName === 'setUp' || $methodName === null;
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

        if (! $this->nameResolver->isName($node->name, 'get')) {
            return false;
        }

        return $this->nodeTypeResolver->isObjectType($node->var, ContainerInterface::class);
    }
}
