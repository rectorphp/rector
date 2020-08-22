<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
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
    public const ERROR_MESSAGE = 'Array explosion is not allow, use value object to pass data instead';

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

        return [self::ERROR_MESSAGE];
    }
}
