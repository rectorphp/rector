<?php

declare(strict_types=1);

namespace Rector\Php70\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\List_;
use PHPStan\Type\StringType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @source http://php.net/manual/en/migration70.incompatible.php#migration70.incompatible.variable-handling.list
 *
 * @see https://stackoverflow.com/a/47965344/1348344
 * @see \Rector\Php70\Tests\Rector\Assign\ListSplitStringRector\ListSplitStringRectorTest
 */
final class ListSplitStringRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'list() cannot split string directly anymore, use str_split()',
            [new CodeSample('list($foo) = "string";', 'list($foo) = str_split("string");')]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Assign::class];
    }

    /**
     * @param Assign $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->var instanceof List_) {
            return null;
        }

        if (! $this->isStaticType($node->expr, StringType::class)) {
            return null;
        }

        $node->expr = $this->nodeFactory->createFuncCall('str_split', [$node->expr]);

        return $node;
    }
}
