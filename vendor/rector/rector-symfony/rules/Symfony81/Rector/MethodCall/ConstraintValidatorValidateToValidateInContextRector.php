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
    private function refactorConstraintValidatorTestCaseCall(MethodCall $methodCall): ?Node
    {
        if ($this->isName($methodCall->name, 'validate') && \count($methodCall->args) === 2) {
            return $this->createThisValidateMethodCall($methodCall->args[0], $methodCall->args[1]);
        }
        if ($this->isName($methodCall->name, 'validateInContext') && \count($methodCall->args) === 3) {
            return $this->createThisValidateMethodCall($methodCall->args[0], $methodCall->args[1]);
        }
        return null;
    }
    private function refactorConstraintValidatorCall(MethodCall $methodCall): ?Node
    {
        if (!$this->isName($methodCall->name, 'validate')) {
            return null;
        }
        if (\count($methodCall->args) !== 2) {
            return null;
        }
        if (!$this->isInsideConstraintValidator($methodCall)) {
            return null;
        }
        $methodCall->name = new Identifier('validateInContext');
        $methodCall->args[] = new Arg($this->nodeFactory->createPropertyFetch(new Variable('this'), 'context'));
        return $methodCall;
    }
    private function createThisValidateMethodCall(Arg $firstArg, Arg $secondArg): MethodCall
    {
        return new MethodCall(new Variable('this'), new Identifier('validate'), [$firstArg, $secondArg]);
    }
    private function isThisValidatorPropertyFetch(Expr $expr): bool
    {
        if (!$expr instanceof PropertyFetch) {
            return \false;
        }
        if (!$this->isName($expr->name, 'validator')) {
            return \false;
        }
        return $expr->var instanceof Variable && $this->isName($expr->var, 'this');
    }
    private function isInsideConstraintValidatorTestCase(MethodCall $methodCall): bool
    {
        return $this->isInsideClassSubclassOf($methodCall, ConstraintValidatorTestCase::class);
    }
    private function isInsideConstraintValidator(MethodCall $methodCall): bool
    {
        return $this->isInsideClassSubclassOf($methodCall, ConstraintValidator::class);
    }
    private function isInsideClassSubclassOf(MethodCall $methodCall, string $className): bool
    {
        $scope = ScopeFetcher::fetch($methodCall);
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
