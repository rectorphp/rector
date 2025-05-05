<?php

declare (strict_types=1);
namespace Rector\Doctrine\DoctrineFixture\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Type\ObjectType;
use Rector\Doctrine\DoctrineFixture\Reflection\ParameterTypeResolver;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\PHPStan\ScopeFetcher;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\DoctrineFixture\Rector\MethodCall\AddGetReferenceTypeRector\AddGetReferenceTypeRectorTest
 *
 * @see https://github.com/doctrine/data-fixtures/pull/409/files
 */
final class AddGetReferenceTypeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ParameterTypeResolver $parameterTypeResolver;
    public function __construct(ParameterTypeResolver $parameterTypeResolver)
    {
        $this->parameterTypeResolver = $parameterTypeResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change $this->getReference() in data fixtures to fill reference class directly', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\DataFixtures\AbstractDataFixture;

final class SomeFixture extends AbstractDataFixture
{
    public function run(SomeEntity $someEntity)
    {
        $someEntity->setSomePassedEntity($this->getReference('some-key'));
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\DataFixtures\AbstractDataFixture;

final class SomeFixture extends AbstractDataFixture
{
    public function run(SomeEntity $someEntity)
    {
        $someEntity->setSomePassedEntity($this->getReference('some-key'), SomeReference::class);
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?\PhpParser\Node\Expr\MethodCall
    {
        if (!$this->isInAbstractFixture($node)) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (\count($node->getArgs()) !== 1) {
            return null;
        }
        $soleArg = $node->getArgs()[0];
        if (!$soleArg->value instanceof MethodCall) {
            return null;
        }
        $nestedMethodCall = $soleArg->value;
        if (!$this->isName($nestedMethodCall->name, 'getReference')) {
            return null;
        }
        // already filled type
        if (\count($nestedMethodCall->getArgs()) === 2) {
            return null;
        }
        $callerParameterObjetType = $this->parameterTypeResolver->resolveCallerFirstParameterObjectType($node);
        if (!$callerParameterObjetType instanceof ObjectType) {
            return null;
        }
        $nestedMethodCall->args[] = new Arg(new ClassConstFetch(new FullyQualified($callerParameterObjetType->getClassName()), 'class'));
        return $node;
    }
    private function isInAbstractFixture(MethodCall $methodCall) : bool
    {
        $scope = ScopeFetcher::fetch($methodCall);
        $classReflection = $scope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \false;
        }
        return $classReflection->is(DoctrineClass::ABSTRACT_FIXTURE);
    }
}
