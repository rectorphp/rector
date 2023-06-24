<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony61\Rector\StaticPropertyFetch;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PHPStan\Reflection\ClassReflection;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
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
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(ReflectionResolver $reflectionResolver)
    {
        $this->reflectionResolver = $reflectionResolver;
    }
    public function getRuleDefinition() : RuleDefinition
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
    public function getNodeTypes() : array
    {
        return [StaticPropertyFetch::class];
    }
    /**
     * @param StaticPropertyFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        $classReflection = $this->reflectionResolver->resolveClassReflection($node);
        if (!$classReflection instanceof ClassReflection) {
            return null;
        }
        if (!$classReflection->isSubclassOf('Symfony\\Component\\Validator\\Constraint')) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($node->name, 'errorNames')) {
            return null;
        }
        $parentClass = $classReflection->getParentClass();
        if (!$parentClass instanceof ClassReflection) {
            return null;
        }
        return $this->nodeFactory->createClassConstFetch($parentClass->getName(), 'ERROR_NAMES');
    }
}
