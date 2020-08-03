<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Exception\ShouldNotHappenException;

/**
 * Useful for prefixed phar bulid, to keep original references to class un-prefixed
 *
 * Basically inversion of this rule:
 * @see https://github.com/symplify/symplify/tree/master/packages/coding-standard#defined-method-argument-should-be-always-constant-value
 *
 * @see \Rector\PHPStanExtensions\Tests\Rule\KeepRectorNamespaceForRectorRule\KeepRectorNamespaceForRectorRuleTest
 */
final class RequireStringArgumentInMethodCallRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Method call "%s()" argument on position %d cannot use "::class" reference';

    /**
     * @var mixed[]
     */
    private $stringArgByMethodByType = [];

    public function __construct(array $constantArgByMethodByType = [])
    {
        $this->stringArgByMethodByType = $constantArgByMethodByType;
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
        // unknown method name
        if (! $node->name instanceof Identifier) {
            return [];
        }

        $errorMessages = [];

        $methodName = (string) $node->name;

        foreach ($this->stringArgByMethodByType as $type => $positionsByMethods) {
            $positions = $this->matchPositions($node, $scope, $type, $positionsByMethods, $methodName);
            if ($positions === null) {
                continue;
            }

            foreach ($node->args as $key => $arg) {
                if ($this->shouldSkipArg($key, $positions, $arg)) {
                    continue;
                }

                $errorMessages[] = sprintf(self::ERROR_MESSAGE, $methodName, $key);
            }
        }

        return $errorMessages;
    }

    /**
     * @param array<string, array<int>> $positionsByMethods
     */
    private function matchPositions(
        MethodCall $methodCall,
        Scope $scope,
        string $desiredType,
        array $positionsByMethods,
        string $methodName
    ): ?array {
        if (! $this->isNodeVarType($methodCall, $scope, $desiredType)) {
            return null;
        }

        return $positionsByMethods[$methodName] ?? null;
    }

    /**
     * @param int[] $positions
     */
    private function shouldSkipArg(int $key, array $positions, Arg $arg): bool
    {
        if (! in_array($key, $positions, true)) {
            return true;
        }

        if (! $arg->value instanceof ClassConstFetch) {
            return true;
        }

        /** @var ClassConstFetch $classConstFetch */
        $classConstFetch = $arg->value;
        $constantName = $classConstFetch->name->toString();

        return $constantName !== 'class';
    }
    private function isNodeVarType(MethodCall $methodCall, Scope $scope, string $desiredType): bool
    {
        if (trait_exists($desiredType)) {
            $message = sprintf(
                'Do not use trait "%s" as type to match, it breaks the matching. Use specific class that is in this trait',
                $desiredType
            );
            throw new ShouldNotHappenException($message);
        }

        $methodVarType = $scope->getType($methodCall->var);
        if (! $methodVarType instanceof TypeWithClassName) {
            return false;
        }

        return is_a($methodVarType->getClassName(), $desiredType, true);
    }
}
