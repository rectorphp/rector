<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony81\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\ObjectType;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use RectorPrefix202606\Symfony\Component\Validator\ConstraintValidator;
use RectorPrefix202606\Symfony\Component\Validator\ConstraintValidatorInterface;
use RectorPrefix202606\Symfony\Component\Validator\Test\ConstraintValidatorTestCase;
use RectorPrefix202606\Symfony\Component\Validator\Validator\ContextualValidatorInterface;
use RectorPrefix202606\Symfony\Component\Validator\Validator\ValidatorInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symfony/symfony/blob/8.1/CHANGELOG.md#validator
 * @see https://symfony.com/blog/new-in-symfony-8-1-validator-improvements
 *
 * @see \Rector\Symfony\Tests\Symfony81\Rector\MethodCall\ConstraintValidatorValidateToValidateInContextRector\ConstraintValidatorValidateToValidateInContextRectorTest
 */
final class ConstraintValidatorValidateToValidateInContextRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrate deprecated ConstraintValidatorInterface::validate() in ConstraintValidatorTestCase tests to $this->validate(), otherwise to validateInContext() (Symfony 8.1+).', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class SomeValidatorTest extends ConstraintValidatorTestCase
{
    public function test(): void
    {
        $this->validator->validate($value, $constraint);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\Validator\Test\ConstraintValidatorTestCase;

final class SomeValidatorTest extends ConstraintValidatorTestCase
{
    public function test(): void
    {
        $this->validate($value, $constraint);
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
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (!$this->isThisValidatorPropertyFetch($node->var)) {
            return null;
        }
        if ($this->isObjectType($node->var, new ObjectType(ContextualValidatorInterface::class))) {
            return null;
        }
        if ($this->isObjectType($node->var, new ObjectType(ValidatorInterface::class))) {
            return null;
        }
        if (!$this->isObjectType($node->var, new ObjectType(ConstraintValidatorInterface::class))) {
            return null;
        }
        if ($this->isInsideConstraintValidatorTestCase($node)) {
            return $this->refactorConstraintValidatorTestCaseCall($node);
        }
        return $this->refactorConstraintValidatorCall($node);
    }
    private function refactorConstraintValidatorTestCaseCall(MethodCall $node): ?Node
    {
        if ($this->isName($node->name, 'validate') && \count($node->args) === 2) {
            return $this->createThisValidateMethodCall($node->args[0], $node->args[1]);
        }
        if ($this->isName($node->name, 'validateInContext') && \count($node->args) === 3) {
            return $this->createThisValidateMethodCall($node->args[0], $node->args[1]);
        }
        return null;
    }
    private function refactorConstraintValidatorCall(MethodCall $node): ?Node
    {
        if (!$this->isName($node->name, 'validate')) {
            return null;
        }
        if (\count($node->args) !== 2) {
            return null;
        }
        if (!$this->isInsideConstraintValidator($node)) {
            return null;
        }
        $node->name = new Identifier('validateInContext');
        $node->args[] = new Arg($this->nodeFactory->createPropertyFetch(new Variable('this'), 'context'));
        return $node;
    }
    /**
     * @param Arg $firstArg
     * @param Arg $secondArg
     */
    private function createThisValidateMethodCall(Arg $firstArg, Arg $secondArg): MethodCall
    {
        return new MethodCall(new Variable('this'), new Identifier('validate'), [$firstArg, $secondArg]);
    }
    private function isThisValidatorPropertyFetch(Expr $node): bool
    {
        if (!$node instanceof PropertyFetch) {
            return \false;
        }
        if (!$this->isName($node->name, 'validator')) {
            return \false;
        }
        return $node->var instanceof Variable && $this->isName($node->var, 'this');
    }
    private function isInsideConstraintValidatorTestCase(MethodCall $node): bool
    {
        return $this->isInsideClassSubclassOf($node, ConstraintValidatorTestCase::class);
    }
    private function isInsideConstraintValidator(MethodCall $node): bool
    {
        return $this->isInsideClassSubclassOf($node, ConstraintValidator::class);
    }
    private function isInsideClassSubclassOf(MethodCall $node, string $className): bool
    {
        $scope = ScopeFetcher::fetch($node);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return \false;
        }
        return $classReflection->isSubclassOfClass($this->reflectionProvider->getClass($className));
    }
}
