<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DoctrineAnnotationGenerated\DataCollector\ResolvedConstantStaticCollector;
use Rector\Utils\DoctrineAnnotationParserSyncer\Contract\Rector\ClassSyncerRectorInterface;

/**
 * @see \Rector\DoctrineAnnotationGenerated\ConstantPreservingDocParser::Constant()
 */
final class LogIdentifierAndResolverValueInConstantClassMethodRector extends AbstractRector implements ClassSyncerRectorInterface
{
    /**
     * @return string[]
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

        // 1. store original value right in the start
        $firstStmt = $node->stmts[0];
        unset($node->stmts[0]);
        $assignExpression = $this->createAssignOriginalIdentifierExpression();
        $node->stmts = array_merge([$firstStmt], [$assignExpression], (array) $node->stmts);

        // 2. record value in each return
        $this->traverseNodesWithCallable((array) $node->stmts, function (Node $node): ?Return_ {
            if (! $node instanceof Return_) {
                return null;
            }

            if ($node->expr === null) {
                return null;
            }

            // assign resolved value to temporary variable
            $resolvedValueVariable = new Variable('resolvedValue');
            $assign = new Assign($resolvedValueVariable, $node->expr);
            $assignExpression = new Expression($assign);

            $this->addNodeBeforeNode($assignExpression, $node);

            // log the value in static call
            $originalIdentifier = new Variable('originalIdentifier');
            $staticCallExpression = $this->createStaticCallExpression($originalIdentifier, $resolvedValueVariable);
            $this->addNodeBeforeNode($staticCallExpression, $node);

            $node->expr = $resolvedValueVariable;
            return $node;
        });

        return $node;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Log original and changed constant value');
    }

    private function createAssignOriginalIdentifierExpression(): Expression
    {
        $originalIdentifier = new Variable('originalIdentifier');
        $identifier = new Variable('identifier');

        $assign = new Assign($originalIdentifier, $identifier);

        return new Expression($assign);
    }

    private function createStaticCallExpression(Variable $identifierVariable, Variable $resolvedVariable): Expression
    {
        $args = [new Arg($identifierVariable), new Arg($resolvedVariable)];
        $staticCall = new StaticCall(new FullyQualified(ResolvedConstantStaticCollector::class), 'collect', $args);

        return new Expression($staticCall);
    }
}
