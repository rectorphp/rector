<?php

declare (strict_types=1);
namespace Rector\Symfony\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Rector\AbstractRector;
use Rector\Symfony\NodeAnalyzer\ValidatorAssert\ConstantExpressionAnalyzer;
use Rector\Symfony\NodeAnalyzer\ValidatorAssert\ConstraintAttributeTargetAnalyzer;
use Rector\Symfony\NodeAnalyzer\ValidatorAssert\ConstraintConstructorAnalyzer;
use Rector\Symfony\NodeAnalyzer\ValidatorAssert\MetadataConstraintResolver;
use Rector\Symfony\NodeFactory\ValidatorAssert\ConstraintAttributeGroupFactory;
use Rector\Symfony\ValueObject\ValidatorAssert\ClassMethodAndConstraint;
use Rector\Symfony\ValueObject\ValidatorAssert\PropertyAndConstraint;
use Rector\VersionBonding\Contract\ComposerPackageConstraintInterface;
use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * The validator loads constraints from PHP attributes since Symfony 5.2, and Doctrine annotations are deprecated
 * since Symfony 6.4
 *
 * @changelog https://symfony.com/doc/current/components/validator/metadata.html
 * @changelog https://symfony.com/doc/current/validation.html#the-basics-of-validation
 *
 * @see \Rector\Symfony\Tests\CodeQuality\Rector\Class_\LoadValidatorMetadataToAttributeRector\LoadValidatorMetadataToAttributeRectorTest
 */
final class LoadValidatorMetadataToAttributeRector extends AbstractRector implements ComposerPackageConstraintInterface
{
    /**
     * @readonly
     */
    private MetadataConstraintResolver $metadataConstraintResolver;
    /**
     * @readonly
     */
    private ConstraintAttributeTargetAnalyzer $constraintAttributeTargetAnalyzer;
    /**
     * @readonly
     */
    private ConstraintAttributeGroupFactory $constraintAttributeGroupFactory;
    /**
     * @readonly
     */
    private ConstantExpressionAnalyzer $constantExpressionAnalyzer;
    /**
     * @readonly
     */
    private ConstraintConstructorAnalyzer $constraintConstructorAnalyzer;
    public function __construct(MetadataConstraintResolver $metadataConstraintResolver, ConstraintAttributeTargetAnalyzer $constraintAttributeTargetAnalyzer, ConstraintAttributeGroupFactory $constraintAttributeGroupFactory, ConstantExpressionAnalyzer $constantExpressionAnalyzer, ConstraintConstructorAnalyzer $constraintConstructorAnalyzer)
    {
        $this->metadataConstraintResolver = $metadataConstraintResolver;
        $this->constraintAttributeTargetAnalyzer = $constraintAttributeTargetAnalyzer;
        $this->constraintAttributeGroupFactory = $constraintAttributeGroupFactory;
        $this->constantExpressionAnalyzer = $constantExpressionAnalyzer;
        $this->constraintConstructorAnalyzer = $constraintConstructorAnalyzer;
    }
    public function provideComposerPackageConstraint(): ComposerPackageConstraint
    {
        return new ComposerPackageConstraint('symfony/validator', '>=5.2');
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Move metadata from `loadValidatorMetadata()` to property/getter/class attributes', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class SomeClass
{
    private $city;

    public static function loadValidatorMetadata(ClassMetadata $metadata): void
    {
        $metadata->addPropertyConstraint('city', new Assert\NotBlank([
            'message' => 'City can\'t be blank.',
        ]));
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;

final class SomeClass
{
    #[Assert\NotBlank(message: 'City can\'t be blank.')]
    private $city;
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
        foreach ($node->stmts as $classStmtKey => $classStmt) {
            if (!$classStmt instanceof ClassMethod) {
                continue;
            }
            if (!$this->isName($classStmt, 'loadValidatorMetadata')) {
                continue;
            }
            if ($classStmt->stmts === null) {
                return null;
            }
            $hasChanged = \false;
            foreach ($classStmt->stmts as $classMethodStmtKey => $methodStmt) {
                // 1. class
                $classConstraintNew = $this->metadataConstraintResolver->resolveClassConstraint($methodStmt);
                if ($classConstraintNew instanceof New_ && $this->refactorClassConstraint($node, $classConstraintNew)) {
                    unset($classStmt->stmts[$classMethodStmtKey]);
                    $hasChanged = \true;
                    continue;
                }
                // 2. getters
                $classMethodAndConstraint = $this->metadataConstraintResolver->resolveGetterConstraint($methodStmt);
                if ($classMethodAndConstraint instanceof ClassMethodAndConstraint && $this->refactorGetterConstraint($node, $classMethodAndConstraint)) {
                    unset($classStmt->stmts[$classMethodStmtKey]);
                    $hasChanged = \true;
                    continue;
                }
                // 3. properties
                $propertyAndConstraint = $this->metadataConstraintResolver->resolvePropertyConstraint($methodStmt);
                if ($propertyAndConstraint instanceof PropertyAndConstraint && $this->refactorPropertyConstraint($node, $propertyAndConstraint)) {
                    unset($classStmt->stmts[$classMethodStmtKey]);
                    $hasChanged = \true;
                }
            }
            if (!$hasChanged) {
                return null;
            }
            // remove the class method, if every constraint moved to an attribute
            if ($classStmt->stmts === []) {
                unset($node->stmts[$classStmtKey]);
            } else {
                $classStmt->stmts = array_values($classStmt->stmts);
            }
            $node->stmts = array_values($node->stmts);
            return $node;
        }
        return null;
    }
    private function refactorClassConstraint(Class_ $class, New_ $constraintNew): bool
    {
        $constraintClass = $this->resolveConstraintClass($constraintNew);
        if ($constraintClass === null) {
            return \false;
        }
        if (!$this->constraintAttributeTargetAnalyzer->supportsClassTarget($constraintClass)) {
            return \false;
        }
        $class->attrGroups[] = $this->constraintAttributeGroupFactory->create($constraintClass, $constraintNew);
        return \true;
    }
    private function refactorGetterConstraint(Class_ $class, ClassMethodAndConstraint $classMethodAndConstraint): bool
    {
        $constraintNew = $classMethodAndConstraint->getConstraintNew();
        $constraintClass = $this->resolveConstraintClass($constraintNew);
        if ($constraintClass === null) {
            return \false;
        }
        if (!$this->constraintAttributeTargetAnalyzer->supportsMethodTarget($constraintClass)) {
            return \false;
        }
        foreach ($classMethodAndConstraint->getPossibleMethodNames() as $possibleMethodName) {
            $classMethod = $class->getMethod($possibleMethodName);
            if (!$classMethod instanceof ClassMethod) {
                continue;
            }
            $classMethod->attrGroups[] = $this->constraintAttributeGroupFactory->create($constraintClass, $constraintNew);
            return \true;
        }
        return \false;
    }
    private function refactorPropertyConstraint(Class_ $class, PropertyAndConstraint $propertyAndConstraint): bool
    {
        $property = $class->getProperty($propertyAndConstraint->getProperty());
        if (!$property instanceof Property) {
            return \false;
        }
        $constraintNew = $propertyAndConstraint->getConstraintNew();
        $constraintClass = $this->resolveConstraintClass($constraintNew);
        if ($constraintClass === null) {
            return \false;
        }
        if (!$this->constraintAttributeTargetAnalyzer->supportsPropertyTarget($constraintClass)) {
            return \false;
        }
        $property->attrGroups[] = $this->constraintAttributeGroupFactory->create($constraintClass, $constraintNew);
        return \true;
    }
    private function resolveConstraintClass(New_ $new): ?string
    {
        // e.g. new Assert\Callback(function () {...}) has no constant expression form, the attribute would not parse
        if (!$this->constantExpressionAnalyzer->areArgsConstant($new->args)) {
            return null;
        }
        $constraintClass = $this->getName($new->class);
        if (!is_string($constraintClass)) {
            return null;
        }
        // e.g. new UniqueUserAlias(['field' => 'alias']) - the options have no matching constructor arguments
        if ($new->args !== [] && !$this->constraintConstructorAnalyzer->hasOwnConstructor($constraintClass)) {
            return null;
        }
        return $constraintClass;
    }
}
