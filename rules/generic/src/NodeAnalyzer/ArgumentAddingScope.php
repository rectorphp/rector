<?php

declare(strict_types=1);

namespace Rector\Generic\NodeAnalyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Generic\ValueObject\ArgumentAdder;
use Rector\NodeNameResolver\NodeNameResolver;

final class ArgumentAddingScope
{
    /**
     * @var string
     */
    public const SCOPE_PARENT_CALL = 'parent_call';

    /**
     * @var string
     */
    public const SCOPE_METHOD_CALL = 'method_call';

    /**
     * @var string
     */
    public const SCOPE_CLASS_METHOD = 'class_method';

    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    public function __construct(NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }

    /**
     * @param ClassMethod|MethodCall|StaticCall $node
     */
    public function isInCorrectScope(Node $node, ArgumentAdder $argumentAdder): bool
    {
        if ($argumentAdder->getScope() === null) {
            return true;
        }

        $scope = $argumentAdder->getScope();

        if ($node instanceof ClassMethod) {
            return $scope === self::SCOPE_CLASS_METHOD;
        }

        if ($node instanceof StaticCall) {
            if (! $node->class instanceof Name) {
                return false;
            }

            if ($this->nodeNameResolver->isName($node->class, 'parent')) {
                return $scope === self::SCOPE_PARENT_CALL;
            }

            return $scope === self::SCOPE_METHOD_CALL;
        }

        // MethodCall
        return $scope === self::SCOPE_METHOD_CALL;
    }
}
