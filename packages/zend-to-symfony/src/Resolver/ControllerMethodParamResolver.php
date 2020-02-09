<?php

declare(strict_types=1);

namespace Rector\ZendToSymfony\Resolver;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Exception\NotImplementedException;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Resolver\NodeNameResolver;
use Rector\Core\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\NodeTypeResolver\Node\AttributeKey;

final class ControllerMethodParamResolver
{
    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(CallableNodeTraverser $callableNodeTraverser, NodeNameResolver $nodeNameResolver)
    {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @return mixed[]
     */
    public function resolve(ClassMethod $classMethod): array
    {
        $parameterNames = [];
        $this->callableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) use (
            &$parameterNames
        ) {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if (! $this->nodeNameResolver->isName($node->var, 'this')) {
                return null;
            }

            if (! $this->nodeNameResolver->isName($node->name, 'getParam')) {
                return null;
            }

            if (! isset($node->args[0])) {
                throw new ShouldNotHappenException();
            }

            if (! $node->args[0]->value instanceof String_) {
                throw new NotImplementedException();
            }

            /** @var String_ $string */
            $string = $node->args[0]->value;
            $parameterName = $string->value;

            /** @var Node|null $parentNode */
            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
            $parameterNames[$parameterName] = $parentNode;
        });

        return $parameterNames;
    }
}
