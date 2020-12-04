<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\TypeWithClassName;
use Symplify\PHPStanRules\Naming\SimpleNameResolver;

/**
 * @todo move to symplify
 * @see \Rector\PHPStanExtensions\Tests\Rule\ForbiddenMethodCallOnTypeRule\ForbiddenMethodCallOnTypeRuleTest
 */
final class ForbiddenMethodCallOnTypeRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = '"%s()" call on "%s" type is not allowed';

    /**
     * @var array<string, string[]>
     */
    private $forbiddenMethodNamesByTypes = [];

    /**
     * @var SimpleNameResolver
     */
    private $simpleNameResolver;

    /**
     * @param array<string, string[]> $forbiddenMethodNamesByTypes
     */
    public function __construct(SimpleNameResolver $simpleNameResolver, array $forbiddenMethodNamesByTypes = [])
    {
        $this->simpleNameResolver = $simpleNameResolver;
        $this->forbiddenMethodNamesByTypes = $forbiddenMethodNamesByTypes;
    }

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        foreach ($this->forbiddenMethodNamesByTypes as $type => $methodsNames) {
            if (! $this->simpleNameResolver->isNames($node->name, $methodsNames)) {
                continue;
            }

            if (! $this->isType($scope, $node->var, $type)) {
                continue;
            }

            $methodName = $this->simpleNameResolver->getName($node->name);
            $errorMessage = sprintf(self::ERROR_MESSAGE, $methodName, $type);

            return [$errorMessage];
        }

        return [];
    }

    private function isType(Scope $scope, Expr $expr, string $desiredType): bool
    {
        $callerType = $scope->getType($expr);
        if (! $callerType instanceof TypeWithClassName) {
            return false;
        }

        return is_a($callerType->getClassName(), $desiredType, true);
    }
}
