<?php

declare(strict_types=1);

namespace Rector\Arguments\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use Rector\Arguments\ValueObject\ArgumentAdder;
use Rector\Core\Enum\ObjectReference;
use Rector\NodeNameResolver\NodeNameResolver;

final class ArgumentAddingScope
{
    /**
     * @var string
     */
    final public const SCOPE_PARENT_CALL = 'parent_call';

    /**
     * @var string
     */
    final public const SCOPE_METHOD_CALL = 'method_call';

    /**
     * @var string
     */
    final public const SCOPE_CLASS_METHOD = 'class_method';

    public function __construct(
        private readonly NodeNameResolver $nodeNameResolver
    ) {
    }

    public function isInCorrectScope(MethodCall | StaticCall $expr, ArgumentAdder $argumentAdder): bool
    {
        if ($argumentAdder->getScope() === null) {
            return true;
        }

        $scope = $argumentAdder->getScope();
        if ($expr instanceof StaticCall) {
            if (! $expr->class instanceof Name) {
                return false;
            }

            if ($this->nodeNameResolver->isName($expr->class, ObjectReference::PARENT()->getValue())) {
                return $scope === self::SCOPE_PARENT_CALL;
            }

            return $scope === self::SCOPE_METHOD_CALL;
        }

        // MethodCall
        return $scope === self::SCOPE_METHOD_CALL;
    }
}
