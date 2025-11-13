<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony61\Rector\StaticPropertyFetch;

use PhpParser\Modifiers;
use PhpParser\Node;
use PhpParser\Node\Const_;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Property;
use PHPStan\Reflection\ClassReflection;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\Symfony\Enum\SymfonyClass;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * Covers:
 * - https://github.com/symfony/symfony/blob/6.1/UPGRADE-6.1.md#validator
 *
 * @see \Rector\Symfony\Tests\Symfony61\Rector\StaticPropertyFetch\ErrorNamesPropertyToConstantRector\ErrorNamesPropertyToConstantRectorTest
 */
final class ErrorNamesPropertyToConstantRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReflectionResolver $reflectionResolver;
    public function __construct(ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Turns old Constraint::$errorNames properties to use Constraint::ERROR_NAMES instead', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Validator\Constraints\NotBlank;

class SomeClass
{
    NotBlank::$errorNames

}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Validator\Constraints\NotBlank;

class SomeClass
{
    NotBlank::ERROR_NAMES

}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [StaticPropertyFetch::class, Class_::class];
    }
    /**
     * @param StaticPropertyFetch|Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        if (!$classReflection->is(SymfonyClass::VALIDATOR_CONSTRAINT)) {
            return null;
        }
        if ($node instanceof StaticPropertyFetch) {
            return $this->refactorStaticPropertyFetch($node, $classReflection);
        }
        foreach ($node->stmts as $key => $stmt) {
            if (!$stmt instanceof Property) {
                continue;
            }
            if (!$stmt->isStatic()) {
                continue;
            }
            if (!$this->isName($stmt->props[0], 'errorNames')) {
                continue;
            }
            $node->stmts[$key] = $this->createClassConst($stmt, $stmt);
            return $node;
        }
        return null;
    }
    private function refactorStaticPropertyFetch(StaticPropertyFetch $staticPropertyFetch, ClassReflection $classReflection): ?ClassConstFetch
    {
        if (!$this->isName($staticPropertyFetch->name, 'errorNames')) {
            return null;
        }
        $parentClass = $classReflection->getParentClass();
        if (!$parentClass instanceof ClassReflection) {
            return null;
        }
        return $this->nodeFactory->createClassConstFetch($parentClass->getName(), 'ERROR_NAMES');
    }
    private function createClassConst(Property $property, Property $stmt): ClassConst
    {
        $propertyItem = $property->props[0];
        $const = new Const_('ERROR_NAMES', $propertyItem->default);
        $classConst = new ClassConst([$const], $stmt->flags & ~Modifiers::STATIC);
        $classConst->setDocComment($property->getDocComment());
        return $classConst;
    }
}
