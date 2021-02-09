<?php

declare(strict_types=1);

namespace Rector\Symfony3\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/symfony/symfony/blob/3.4/UPGRADE-3.0.md#form
 *
 * @see \Rector\Symfony3\Tests\Rector\ClassMethod\RemoveDefaultGetBlockPrefixRector\RemoveDefaultGetBlockPrefixRectorTest
 */
final class RemoveDefaultGetBlockPrefixRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Rename `getBlockPrefix()` if it returns the default value - class to underscore, e.g. UserFormType = user_form',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Symfony\Component\Form\AbstractType;

class TaskType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'task';
    }
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
use Symfony\Component\Form\AbstractType;

class TaskType extends AbstractType
{
}
CODE_SAMPLE
            ),
            ]
        );
    }

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
        if (! $this->isObjectMethodNameMatch($node)) {
            return null;
        }

        $returnedExpr = $this->resolveOnlyStmtReturnExpr($node);
        if (! $returnedExpr instanceof Expr) {
            return null;
        }

        $returnedValue = $this->valueResolver->getValue($returnedExpr);

        $className = $node->getAttribute(AttributeKey::CLASS_NAME);
        if (! is_string($className)) {
            return null;
        }

        $shortClassName = $this->nodeNameResolver->getShortName($className);
        if (Strings::endsWith($shortClassName, 'Type')) {
            $shortClassName = (string) Strings::before($shortClassName, 'Type');
        }

        $underscoredClassShortName = StaticRectorStrings::camelCaseToUnderscore($shortClassName);
        if ($underscoredClassShortName !== $returnedValue) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }

    private function isObjectMethodNameMatch(ClassMethod $classMethod): bool
    {
        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if (! $classLike instanceof Class_) {
            return false;
        }

        if (! $this->isObjectType($classMethod, 'Symfony\Component\Form\AbstractType')) {
            return false;
        }

        return $this->isName($classMethod->name, 'getBlockPrefix');
    }

    /**
     * return <$thisValue>;
     */
    private function resolveOnlyStmtReturnExpr(ClassMethod $classMethod): ?Expr
    {
        if (count((array) $classMethod->stmts) !== 1) {
            return null;
        }

        if (! isset($classMethod->stmts[0])) {
            throw new ShouldNotHappenException();
        }

        $onlyStmt = $classMethod->stmts[0];
        if (! $onlyStmt instanceof Return_) {
            return null;
        }

        return $onlyStmt->expr;
    }
}
