<?php declare(strict_types=1);

namespace Rector\ZendToSymfony\Resolver;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Exception\NotImplementedException;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;

final class ControllerMethodParamResolver
{
    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(CallableNodeTraverser $callableNodeTraverser, NameResolver $nameResolver)
    {
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->nameResolver = $nameResolver;
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

            if (! $this->nameResolver->isName($node->var, 'this')) {
                return null;
            }

            if (! $this->nameResolver->isName($node->name, 'getParam')) {
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
