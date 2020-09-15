<?php

declare(strict_types=1);

namespace Rector\Symfony\Rector\ClassMethod;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\Util\StaticRectorStrings;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://github.com/symfony/symfony/blob/3.4/UPGRADE-3.0.md#form
 *
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\RemoveDefaultGetBlockPrefixRector\RemoveDefaultGetBlockPrefixRectorTest
 */
final class RemoveDefaultGetBlockPrefixRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
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
        if ($returnedExpr === null) {
            return null;
        }

        $returnedValue = $this->getValue($returnedExpr);

        $classShortName = $node->getAttribute(AttributeKey::CLASS_SHORT_NAME);
        if (Strings::endsWith($classShortName, 'Type')) {
            $classShortName = Strings::before($classShortName, 'Type');
        }

        $underscoredClassShortName = StaticRectorStrings::camelCaseToUnderscore($classShortName);
        if ($underscoredClassShortName !== $returnedValue) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }

    private function isObjectMethodNameMatch(ClassMethod $classMethod): bool
    {
        if (! $this->isInObjectType($classMethod, 'Symfony\Component\Form\AbstractType')) {
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

        $onlyStmt = $classMethod->stmts[0];
        if (! $onlyStmt instanceof Return_) {
            return null;
        }

        return $onlyStmt->expr;
    }
}
