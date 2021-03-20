<?php

declare(strict_types=1);

namespace Rector\Utils\DoctrineAnnotationParserSyncer\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Rector\AbstractRector;
use Rector\DoctrineAnnotationGenerated\DataCollector\ResolvedConstantStaticCollector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Utils\DoctrineAnnotationParserSyncer\Contract\Rector\ClassSyncerRectorInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\DoctrineAnnotationGenerated\ConstantPreservingDocParser::Constant()
 */
final class LogIdentifierAndResolverValueInConstantClassMethodRector extends AbstractRector implements ClassSyncerRectorInterface
{
    /**
     * @return array<class-string<\PhpParser\Node>>
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
        /** @var Scope $scope */
        $scope = $node->getAttribute(AttributeKey::SCOPE);

        $classReflection = $scope->getClassReflection();
        if (! $classReflection instanceof ClassReflection) {
            return null;
        }

        if ($classReflection->getName() !== 'Doctrine\Common\Annotations\DocParser') {
            return null;
        }

        if (! $this->isName($node->name, 'Constant')) {
            return null;
        }

        // 1. store original value right in the start
        if (! isset($node->stmts[0])) {
            return null;
        }

        $firstStmt = $node->stmts[0];

        unset($node->stmts[0]);
        $assignExpression = $this->createAssignOriginalIdentifierExpression();
        $node->stmts = array_merge([$firstStmt], [$assignExpression], (array) $node->stmts);

        // 2. record value in each return
        $this->traverseNodesWithCallable($node->stmts, function (Node $node): ?Return_ {
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

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Log original and changed constant value', [
            new CodeSample(
                <<<'CODE_SAMPLE'
namespace Doctrine\Common\Annotations;

class AnnotationReader
{
    public function Constant()
    {
        // ...
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
namespace Doctrine\Common\Annotations;

class AnnotationReader
{
    public function Constant()
    {
        $identifier = $this->Identifier();
        $originalIdentifier = $identifier;
        // ...
    }
}
CODE_SAMPLE
            ),
        ]);
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
        $arguments = [$identifierVariable, $resolvedVariable];
        $staticCall = $this->nodeFactory->createStaticCall(
            ResolvedConstantStaticCollector::class,
            'collect',
            $arguments
        );

        return new Expression($staticCall);
    }
}
