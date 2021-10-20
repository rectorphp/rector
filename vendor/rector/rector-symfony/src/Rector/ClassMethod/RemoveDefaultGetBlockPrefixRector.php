<?php

declare (strict_types=1);
namespace Rector\Symfony\Rector\ClassMethod;

use RectorPrefix20211020\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20211020\Stringy\Stringy;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/symfony/symfony/blob/3.4/UPGRADE-3.0.md#form
 *
 * @see \Rector\Symfony\Tests\Rector\ClassMethod\RemoveDefaultGetBlockPrefixRector\RemoveDefaultGetBlockPrefixRectorTest
 */
final class RemoveDefaultGetBlockPrefixRector extends \Rector\Core\Rector\AbstractRector
{
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Rename `getBlockPrefix()` if it returns the default value - class to underscore, e.g. UserFormType = user_form', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Form\AbstractType;

class TaskType extends AbstractType
{
    public function getBlockPrefix()
    {
        return 'task';
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Form\AbstractType;

class TaskType extends AbstractType
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectMethodNameMatch($node)) {
            return null;
        }
        $returnedExpr = $this->resolveOnlyStmtReturnExpr($node);
        if (!$returnedExpr instanceof \PhpParser\Node\Expr) {
            return null;
        }
        $returnedValue = $this->valueResolver->getValue($returnedExpr);
        $className = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        if (!\is_string($className)) {
            return null;
        }
        $shortClassName = $this->nodeNameResolver->getShortName($className);
        if (\substr_compare($shortClassName, 'Type', -\strlen('Type')) === 0) {
            $shortClassName = (string) \RectorPrefix20211020\Nette\Utils\Strings::before($shortClassName, 'Type');
        }
        $stringy = new \RectorPrefix20211020\Stringy\Stringy($shortClassName);
        $underscoredClassShortName = (string) $stringy->underscored();
        if ($underscoredClassShortName !== $returnedValue) {
            return null;
        }
        $this->removeNode($node);
        return null;
    }
    private function isObjectMethodNameMatch(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $classLike = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
            return \false;
        }
        if (!$this->isObjectType($classMethod, new \PHPStan\Type\ObjectType('Symfony\\Component\\Form\\AbstractType'))) {
            return \false;
        }
        return $this->isName($classMethod->name, 'getBlockPrefix');
    }
    /**
     * return <$thisValue>;
     */
    private function resolveOnlyStmtReturnExpr(\PhpParser\Node\Stmt\ClassMethod $classMethod) : ?\PhpParser\Node\Expr
    {
        if (\count((array) $classMethod->stmts) !== 1) {
            return null;
        }
        if (!isset($classMethod->stmts[0])) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $onlyStmt = $classMethod->stmts[0];
        if (!$onlyStmt instanceof \PhpParser\Node\Stmt\Return_) {
            return null;
        }
        return $onlyStmt->expr;
    }
}
