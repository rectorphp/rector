<?php

declare(strict_types=1);

namespace Rector\DowngradePhp80\Rector\Class_;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://wiki.php.net/rfc/throw_expression
 *
 * @see \Rector\DowngradePhp80\Tests\Rector\Class_\DowngradeThrowExpressionRector\DowngradeThrowExpressionRectorTest
 */
final class DowngradeThrowExpressionRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change constructor property promotion to property asssign', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function __construct($nullableValue)
    {
        return $nullableValue ?? throw new InvalidArgumentException()
    }
}
PHP

                ,
                <<<'PHP'
class SomeClass
{
    public function __construct($nullableValue)
    {
        if ($nullableValue !== null) {
            return $nullableValue;
        }

        throw new InvalidArgumentException()
    }
}
PHP

            )
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }

    /**
     * @param \PhpParser\Node\Stmt\Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        // change the node

        return $node;
    }
}
