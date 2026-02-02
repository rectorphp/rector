<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\Expression;

use PhpParser\Node\Stmt\Class_;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Doctrine\NodeAnalyzer\DoctrineEntityDetector;
use Rector\PhpParser\AstResolver;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\Expression\ConfiguredMockEntityToSetterObjectRector\ConfiguredMockEntityToSetterObjectRectorTest
 */
final class ConfiguredMockEntityToSetterObjectRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private AstResolver $astResolver;
    /**
     * @readonly
     */
    private DoctrineEntityDetector $doctrineEntityDetector;
    public function __construct(ReflectionProvider $reflectionProvider, TestsNodeAnalyzer $testsNodeAnalyzer, ValueResolver $valueResolver, AstResolver $astResolver, DoctrineEntityDetector $doctrineEntityDetector)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->valueResolver = $valueResolver;
        $this->astResolver = $astResolver;
        $this->doctrineEntityDetector = $doctrineEntityDetector;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change createConfigureMock() on Entity/Document object to direct new instance with setters', [new CodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestClass
{
    public function test()
    {
        $someObject = $this->createConfiguredMock(SomeObject::class, [
            'name' => 'John',
            'surname' => 'Doe',
        ]);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeTest extends TestClass
{
    public function test()
    {
        $someObject = new SomeObject();
        $someObject->setName('John');
        $someObject->setSurname('Doe');
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
        return [Expression::class];
    }
    /**
     * @param Expression $node
     * @return Expression[]|null
     */
    public function refactor(Node $node): ?array
    {
        if (!$this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }
        if (!$node->expr instanceof Assign) {
            return null;
        }
        $assign = $node->expr;
        if (!$assign->expr instanceof MethodCall) {
            return null;
        }
        $objectVariable = $assign->var;
        $methodCall = $assign->expr;
        if (!$this->isName($methodCall->name, 'createConfiguredMock')) {
            return null;
        }
        if ($methodCall->isFirstClassCallable()) {
            return null;
        }
        $mockedClassArg = $methodCall->getArgs()[0];
        $doctrineClass = $this->matchDoctrineClassName($mockedClassArg->value);
        if (!is_string($doctrineClass)) {
            return null;
        }
        $definedGettersArg = $methodCall->getArgs()[1];
        if (!$definedGettersArg->value instanceof Array_) {
            return null;
        }
        $assign->expr = new New_(new FullyQualified($doctrineClass));
        $setterExpressions = $this->createEntitySetterExpressions($definedGettersArg->value, $objectVariable);
        return array_merge([$node], $setterExpressions);
    }
    /**
     * @return Expression[]
     */
    private function createEntitySetterExpressions(Array_ $definedGettersArray, Expr $expr): array
    {
        $setterExpressions = [];
        foreach ($definedGettersArray->items as $arrayItem) {
            if (!$arrayItem->key instanceof Expr) {
                continue;
            }
            $getterName = $this->valueResolver->getValue($arrayItem->key);
            if (!is_string($getterName)) {
                continue;
            }
            // remove "get" prefix
            if (strncmp($getterName, 'get', strlen('get')) !== 0) {
                continue;
            }
            $setterName = 'set' . substr($getterName, 3);
            $setterMethodCall = new MethodCall($expr, $setterName, [new Arg($arrayItem->value)]);
            $setterExpressions[] = new Expression($setterMethodCall);
        }
        return $setterExpressions;
    }
    private function matchDoctrineClassName(Expr $expr): ?string
    {
        $mockedClassValue = $this->valueResolver->getValue($expr);
        if (!is_string($mockedClassValue)) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($mockedClassValue)) {
            return null;
        }
        $mockedClass = $this->astResolver->resolveClassFromName($mockedClassValue);
        if (!$mockedClass instanceof Class_) {
            return null;
        }
        if (!$this->doctrineEntityDetector->detect($mockedClass)) {
            return null;
        }
        return $mockedClassValue;
    }
}
