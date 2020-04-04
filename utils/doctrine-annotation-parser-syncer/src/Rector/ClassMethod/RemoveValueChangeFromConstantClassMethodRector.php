<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\If_;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Utils\DoctrineAnnotationParserSyncer\Contract\Rector\ClassSyncerRectorInterface;

final class RemoveValueChangeFromConstantClassMethodRector extends AbstractRector implements ClassSyncerRectorInterface
{
    /**
     * @return class-string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInClassNamed($node, 'Doctrine\Common\Annotations\DocParser')) {
            return null;
        }

        if (! $this->isName($node->name, 'Constant')) {
            return null;
        }

        $identifierVariable = new Variable('identifier');

        $ifTrue = $this->createIfWithStringToBool($identifierVariable, 'true');
        $ifFalse = $this->createIfWithStringToBool($identifierVariable, 'false');
        $ifNull = $this->createIfWithStringToBool($identifierVariable, 'null');
        $returnExpression = new Return_($identifierVariable);

        $node->stmts = [$node->stmts[0], $ifTrue, $ifFalse, $ifNull, $returnExpression];

        return $node;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Remove value change from Constant() class method');
    }

    private function createIfWithStringToBool(Variable $variable, string $value): If_
    {
        $identical = new Identical($variable, new String_($value));
        $if = new If_($identical);
        $if->stmts[] = new Return_(new ConstFetch(new Name($value)));

        return $if;
    }
}
