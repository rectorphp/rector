<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

/**
 * @todo make part of core symplify
 * @see \Rector\PHPStanExtensions\Tests\Rule\ForbiddenArrayDestructRule\ForbiddenArrayDestructRuleTest
 */
final class ForbiddenArrayDestructRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Array explosion is not allowed. Use value object to pass data instead';

    public function getNodeType(): string
    {
        return Assign::class;
    }

    /**
     * @param Assign $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if (! $node->var instanceof Array_) {
            return [];
        }

        // swaps are allowed
        if ($node->expr instanceof Array_) {
            return [];
        }

        // "explode()" is allowed
        if ($node->expr instanceof FuncCall && $this->isName($node->expr, 'explode')) {
            return [];
        }

        return [self::ERROR_MESSAGE];
    }

    private function isName(FuncCall $funcCall, string $desiredName): bool
    {
        if (! $funcCall->name instanceof Name) {
            return false;
        }

        return $funcCall->name->toString() === $desiredName;
    }
}
