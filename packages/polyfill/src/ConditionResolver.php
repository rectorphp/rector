<?php

declare(strict_types=1);

namespace Rector\Polyfill;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\FuncCall;
use Rector\Core\Php\PhpVersionProvider;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
use Rector\Polyfill\Contract\ConditionInterface;
use Rector\Polyfill\ValueObject\BinaryToVersionCompareCondition;
use Rector\Polyfill\ValueObject\VersionCompareCondition;

final class ConditionResolver
{
    /**
     * @var NodeNameResolver
     */
    private $nodeNameResolver;

    /**
     * @var ValueResolver
     */
    private $valueResolver;

    /**
     * @var PhpVersionProvider
     */
    private $phpVersionProvider;

    public function __construct(
        NodeNameResolver $nodeNameResolver,
        PhpVersionProvider $phpVersionProvider,
        ValueResolver $valueResolver
    ) {
        $this->nodeNameResolver = $nodeNameResolver;
        $this->valueResolver = $valueResolver;
        $this->phpVersionProvider = $phpVersionProvider;
    }

    public function resolveFromExpr(Expr $expr): ?ConditionInterface
    {
        if ($this->isVersionCompareFuncCall($expr)) {
            /** @var FuncCall $expr */
            return $this->resolveVersionCompareConditionForFuncCall($expr);
        }

        if (! $expr instanceof Identical && ! $expr instanceof Equal && ! $expr instanceof NotIdentical && ! $expr instanceof NotEqual) {
            return null;
        }

        $binaryClass = get_class($expr);

        if ($this->isVersionCompareFuncCall($expr->left)) {
            /** @var FuncCall $funcCall */
            $funcCall = $expr->left;
            return $this->resolveFuncCall($funcCall, $expr->right, $binaryClass);
        }

        if ($this->isVersionCompareFuncCall($expr->right)) {
            /** @var FuncCall $funcCall */
            $funcCall = $expr->right;

            $versionCompareCondition = $this->resolveVersionCompareConditionForFuncCall($funcCall);
            if ($versionCompareCondition === null) {
                return null;
            }

            $expectedValue = $this->valueResolver->getValue($expr->left);

            return new BinaryToVersionCompareCondition($versionCompareCondition, $binaryClass, $expectedValue);
        }

        return null;
    }

    private function isVersionCompareFuncCall(Node $node): bool
    {
        if (! $node instanceof FuncCall) {
            return false;
        }

        return $this->nodeNameResolver->isName($node, 'version_compare');
    }

    private function resolveVersionCompareConditionForFuncCall(FuncCall $funcCall): ?VersionCompareCondition
    {
        $firstVersion = $this->resolveArgumentValue($funcCall, 0);
        if ($firstVersion === null) {
            return null;
        }

        $secondVersion = $this->resolveArgumentValue($funcCall, 1);
        if ($secondVersion === null) {
            return null;
        }

        // includes compare sign as 3rd argument
        $versionCompareSign = null;
        if (isset($funcCall->args[2])) {
            $versionCompareSign = $this->valueResolver->getValue($funcCall->args[2]->value);
        }

        return new VersionCompareCondition($firstVersion, $secondVersion, $versionCompareSign);
    }

    private function resolveFuncCall(
        FuncCall $funcCall,
        Expr $expr,
        string $binaryClass
    ): ?BinaryToVersionCompareCondition {
        $versionCompareCondition = $this->resolveVersionCompareConditionForFuncCall($funcCall);
        if ($versionCompareCondition === null) {
            return null;
        }

        $expectedValue = $this->valueResolver->getValue($expr);

        return new BinaryToVersionCompareCondition($versionCompareCondition, $binaryClass, $expectedValue);
    }

    private function resolveArgumentValue(FuncCall $funcCall, int $argumentPosition): ?string
    {
        /** @var string|null $version */
        $version = $this->valueResolver->getValue($funcCall->args[$argumentPosition]->value);
        if ($version === 'PHP_VERSION') {
            return $this->phpVersionProvider->provide();
        }

        return $version;
    }
}
