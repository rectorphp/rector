<?php

declare (strict_types=1);
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
     * @readonly
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $expr
     */
    public function isInCorrectScope($expr, \Rector\Arguments\ValueObject\ArgumentAdder $argumentAdder) : bool
    {
        if ($argumentAdder->getScope() === null) {
            return \true;
        }
        $scope = $argumentAdder->getScope();
        if ($expr instanceof \PhpParser\Node\Expr\StaticCall) {
            if (!$expr->class instanceof \PhpParser\Node\Name) {
                return \false;
            }
            if ($this->nodeNameResolver->isName($expr->class, \Rector\Core\Enum\ObjectReference::PARENT)) {
                return $scope === self::SCOPE_PARENT_CALL;
            }
            return $scope === self::SCOPE_METHOD_CALL;
        }
        // MethodCall
        return $scope === self::SCOPE_METHOD_CALL;
    }
}
