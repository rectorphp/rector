<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony42\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\ArrayItem;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Rector\RuleDoc\CodeSample\CodeSample;
use Rector\RuleDoc\RuleDefinition;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
/**
 * @see \Rector\Symfony\Tests\Symfony42\Rector\Class_\GetExtendedTypesReturnArrayRector\GetExtendedTypesReturnArrayRectorTest
 */
final class GetExtendedTypesReturnArrayRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('symfony/form', '>=4.2');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Wrap return value of getExtendedTypes() in form type extension to array', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;

final class SomeTypeExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return FormType::class;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;

final class SomeTypeExtension extends AbstractTypeExtension
{
    public static function getExtendedTypes(): iterable
    {
        return [FormType::class];
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $classMethod = $node->getMethod('getExtendedTypes');
        if (!$classMethod instanceof ClassMethod || $classMethod->stmts === null) {
            return null;
        }
        if (!$this->isObjectType($node, new ObjectType('Symfony\Component\Form\AbstractTypeExtension'))) {
            return null;
        }
        $hasChanged = \false;
        foreach ($classMethod->stmts as $stmt) {
            if (!$stmt instanceof Return_ || !$stmt->expr instanceof Expr || $stmt->expr instanceof Array_) {
                continue;
            }
            $stmt->expr = new Array_([new ArrayItem($stmt->expr)]);
            $hasChanged = \true;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
