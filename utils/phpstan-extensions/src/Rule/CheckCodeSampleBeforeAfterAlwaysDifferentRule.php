<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;

/**
 * @see \Rector\PHPStanExtensions\Tests\Rule\CheckCodeSampleBeforeAfterAlwaysDifferentRule\CheckCodeSampleBeforeAfterAlwaysDifferentRuleTest
 */
final class CheckCodeSampleBeforeAfterAlwaysDifferentRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR_MESSAGE = 'Code sample before and after must be different';

    public function getNodeType(): string
    {
        return New_::class;
    }

    /**
     * @param New_ $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        $class = $node->class;
        if (! $class instanceof FullyQualified) {
            return [];
        }

        $className = $class->toString();
        if ($className !== CodeSample::class) {
            return [];
        }

        $args = $node->args;
        /** @var String_ $firstParameter */
        $firstParameter = $args[0]->value;
        /** @var String_ $secondParameter */
        $secondParameter = $args[1]->value;

        if ($firstParameter->value !== $secondParameter->value) {
            return [];
        }

        return [self::ERROR_MESSAGE];
    }
}
