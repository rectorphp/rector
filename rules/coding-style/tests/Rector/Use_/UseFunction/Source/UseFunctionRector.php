<?php

declare(strict_types=1);

namespace Rector\CodingStyle\Tests\Rector\Use_\UseFunction\Source;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use Rector\Core\PhpParser\Node\CustomNode\FileWithoutNamespace;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

class UseFunctionRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [FileWithoutNamespace::class];
    }

    /**
     * @param FileWithoutNamespace $node
     * @return Node|null
     */
    public function refactor(Node $node): ?Node
    {
        $newStmts = [];
        foreach ($node->stmts as $stmt) {
            $newStmts[] = $stmt;
            if (!$stmt instanceof Expression) {
                continue;
            }

            if (!$stmt->expr instanceof FuncCall) {
                continue;
            }

            if ($this->getName($stmt->expr->name) !== 'Deployer\set') {
                continue;
            }

            /** @var String_ $firstArg */
            $firstArg = $stmt->expr->args[0]->value;
            if ($firstArg->value === 'my_key') {
                $newStmts[] = new Expression(
                    new FuncCall(new FullyQualified('Deployer\set'), [
                        new Arg(new String_('my_key_2')),
                        new Arg(new String_('my_value_2')),
                    ])
                );
            }
        }

        $node->stmts = $newStmts;
        return $node;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Adds set my_key_2 to deploy.php', [
                new CodeSample("
                    use function Deployer\set;

                    set('my_key', 'my_value');
                    ",
                    "
                    use function Deployer\set;

                    set('my_key', 'my_value');
                    set('my_key_2', 'my_value_2');
                    "
                )
            ]
        );
    }
}
