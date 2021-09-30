<?php

declare (strict_types=1);
namespace Rector\PhpSpecToPHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpSpecToPHPUnit\MatchersManipulator;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\NodeFactory\AssertMethodCallFactory;
use Rector\PhpSpecToPHPUnit\NodeFactory\BeConstructedWithAssignFactory;
use Rector\PhpSpecToPHPUnit\NodeFactory\DuringMethodCallFactory;
use Rector\PhpSpecToPHPUnit\Rector\AbstractPhpSpecToPHPUnitRector;
/**
 * @see \Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\PhpSpecToPHPUnitRectorTest
 */
final class PhpSpecPromisesToPHPUnitAssertRector extends \Rector\PhpSpecToPHPUnit\Rector\AbstractPhpSpecToPHPUnitRector
{
    /**
     * @changelog https://github.com/phpspec/phpspec/blob/master/src/PhpSpec/Wrapper/Subject.php
     * ↓
     * @changelog https://phpunit.readthedocs.io/en/8.0/assertions.html
     * @var array<string, string[]>
     */
    private const NEW_METHOD_TO_OLD_METHODS = [
        'assertInstanceOf' => ['shouldBeAnInstanceOf', 'shouldHaveType', 'shouldReturnAnInstanceOf'],
        'assertSame' => ['shouldBe', 'shouldReturn'],
        'assertNotSame' => ['shouldNotBe', 'shouldNotReturn'],
        'assertCount' => ['shouldHaveCount'],
        'assertEquals' => ['shouldBeEqualTo', 'shouldEqual'],
        'assertNotEquals' => ['shouldNotBeEqualTo'],
        'assertContains' => ['shouldContain'],
        'assertNotContains' => ['shouldNotContain'],
        // types
        'assertIsIterable' => ['shouldBeArray'],
        'assertIsNotIterable' => ['shouldNotBeArray'],
        'assertIsString' => ['shouldBeString'],
        'assertIsNotString' => ['shouldNotBeString'],
        'assertIsBool' => ['shouldBeBool', 'shouldBeBoolean'],
        'assertIsNotBool' => ['shouldNotBeBool', 'shouldNotBeBoolean'],
        'assertIsCallable' => ['shouldBeCallable'],
        'assertIsNotCallable' => ['shouldNotBeCallable'],
        'assertIsFloat' => ['shouldBeDouble', 'shouldBeFloat'],
        'assertIsNotFloat' => ['shouldNotBeDouble', 'shouldNotBeFloat'],
        'assertIsInt' => ['shouldBeInt', 'shouldBeInteger'],
        'assertIsNotInt' => ['shouldNotBeInt', 'shouldNotBeInteger'],
        'assertIsNull' => ['shouldBeNull'],
        'assertIsNotNull' => ['shouldNotBeNull'],
        'assertIsNumeric' => ['shouldBeNumeric'],
        'assertIsNotNumeric' => ['shouldNotBeNumeric'],
        'assertIsObject' => ['shouldBeObject'],
        'assertIsNotObject' => ['shouldNotBeObject'],
        'assertIsResource' => ['shouldBeResource'],
        'assertIsNotResource' => ['shouldNotBeResource'],
        'assertIsScalar' => ['shouldBeScalar'],
        'assertIsNotScalar' => ['shouldNotBeScalar'],
        'assertNan' => ['shouldBeNan'],
        'assertFinite' => ['shouldBeFinite', 'shouldNotBeFinite'],
        'assertInfinite' => ['shouldBeInfinite', 'shouldNotBeInfinite'],
    ];
    /**
     * @var string
     */
    private const THIS = 'this';
    /**
     * @var string|null
     */
    private $testedClass;
    /**
     * @var bool
     */
    private $isPrepared = \false;
    /**
     * @var string[]
     */
    private $matchersKeys = [];
    /**
     * @var \PhpParser\Node\Expr\PropertyFetch|null
     */
    private $testedObjectPropertyFetch;
    /**
     * @var \Rector\PhpSpecToPHPUnit\MatchersManipulator
     */
    private $matchersManipulator;
    /**
     * @var \Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming
     */
    private $phpSpecRenaming;
    /**
     * @var \Rector\PhpSpecToPHPUnit\NodeFactory\AssertMethodCallFactory
     */
    private $assertMethodCallFactory;
    /**
     * @var \Rector\PhpSpecToPHPUnit\NodeFactory\BeConstructedWithAssignFactory
     */
    private $beConstructedWithAssignFactory;
    /**
     * @var \Rector\PhpSpecToPHPUnit\NodeFactory\DuringMethodCallFactory
     */
    private $duringMethodCallFactory;
    public function __construct(\Rector\PhpSpecToPHPUnit\MatchersManipulator $matchersManipulator, \Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming $phpSpecRenaming, \Rector\PhpSpecToPHPUnit\NodeFactory\AssertMethodCallFactory $assertMethodCallFactory, \Rector\PhpSpecToPHPUnit\NodeFactory\BeConstructedWithAssignFactory $beConstructedWithAssignFactory, \Rector\PhpSpecToPHPUnit\NodeFactory\DuringMethodCallFactory $duringMethodCallFactory)
    {
        $this->matchersManipulator = $matchersManipulator;
        $this->phpSpecRenaming = $phpSpecRenaming;
        $this->assertMethodCallFactory = $assertMethodCallFactory;
        $this->beConstructedWithAssignFactory = $beConstructedWithAssignFactory;
        $this->duringMethodCallFactory = $duringMethodCallFactory;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $this->isPrepared = \false;
        $this->matchersKeys = [];
        if (!$this->isInPhpSpecBehavior($node)) {
            return null;
        }
        if ($this->isName($node->name, 'getWrappedObject')) {
            return $node->var;
        }
        if ($this->isName($node->name, 'during')) {
            return $this->duringMethodCallFactory->create($node, $this->getTestedObjectPropertyFetch());
        }
        if ($this->isName($node->name, 'duringInstantiation')) {
            return $this->processDuringInstantiation($node);
        }
        // skip reserved names
        if ($this->isNames($node->name, ['getMatchers', 'expectException', 'assert*'])) {
            return null;
        }
        $this->prepareMethodCall($node);
        if ($this->isName($node->name, 'beConstructed*')) {
            return $this->beConstructedWithAssignFactory->create($node, $this->getTestedClass(), $this->getTestedObjectPropertyFetch());
        }
        $this->processMatchersKeys($node);
        $args = $node->args;
        foreach (self::NEW_METHOD_TO_OLD_METHODS as $newMethod => $oldMethods) {
            if (!$this->isNames($node->name, $oldMethods)) {
                continue;
            }
            return $this->assertMethodCallFactory->createAssertMethod($newMethod, $node->var, $args[0]->value ?? null, $this->getTestedObjectPropertyFetch());
        }
        if ($this->shouldSkip($node)) {
            return null;
        }
        if ($this->isName($node->name, 'clone')) {
            return new \PhpParser\Node\Expr\Clone_($this->getTestedObjectPropertyFetch());
        }
        $methodName = $this->getName($node->name);
        if ($methodName === null) {
            return null;
        }
        /** @var Class_ $classLike */
        $classLike = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        $classMethod = $classLike->getMethod($methodName);
        // it's a method call, skip
        if ($classMethod !== null) {
            return null;
        }
        $node->var = $this->getTestedObjectPropertyFetch();
        return $node;
    }
    private function processDuringInstantiation(\PhpParser\Node\Expr\MethodCall $methodCall) : \PhpParser\Node\Expr\MethodCall
    {
        /** @var MethodCall $parentMethodCall */
        $parentMethodCall = $methodCall->var;
        $parentMethodCall->name = new \PhpParser\Node\Identifier('expectException');
        return $parentMethodCall;
    }
    private function prepareMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : void
    {
        if ($this->isPrepared) {
            return;
        }
        /** @var Class_ $classLike */
        $classLike = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        $this->matchersKeys = $this->matchersManipulator->resolveMatcherNamesFromClass($classLike);
        $this->testedClass = $this->phpSpecRenaming->resolveTestedClass($methodCall);
        $this->testedObjectPropertyFetch = $this->createTestedObjectPropertyFetch($classLike);
        $this->isPrepared = \true;
    }
    private function getTestedObjectPropertyFetch() : \PhpParser\Node\Expr\PropertyFetch
    {
        if ($this->testedObjectPropertyFetch === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return $this->testedObjectPropertyFetch;
    }
    private function getTestedClass() : string
    {
        if ($this->testedClass === null) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        return $this->testedClass;
    }
    /**
     * @changelog https://johannespichler.com/writing-custom-phpspec-matchers/
     */
    private function processMatchersKeys(\PhpParser\Node\Expr\MethodCall $methodCall) : void
    {
        foreach ($this->matchersKeys as $matcherKey) {
            if (!$this->isName($methodCall->name, 'should' . \ucfirst($matcherKey))) {
                continue;
            }
            if (!$methodCall->var instanceof \PhpParser\Node\Expr\MethodCall) {
                continue;
            }
            // 1. assign callable to variable
            $thisGetMatchers = $this->nodeFactory->createMethodCall(self::THIS, 'getMatchers');
            $arrayDimFetch = new \PhpParser\Node\Expr\ArrayDimFetch($thisGetMatchers, new \PhpParser\Node\Scalar\String_($matcherKey));
            $matcherCallableVariable = new \PhpParser\Node\Expr\Variable('matcherCallable');
            $assign = new \PhpParser\Node\Expr\Assign($matcherCallableVariable, $arrayDimFetch);
            // 2. call it on result
            $funcCall = new \PhpParser\Node\Expr\FuncCall($matcherCallableVariable);
            $funcCall->args = $methodCall->args;
            $methodCall->name = $methodCall->var->name;
            $methodCall->var = $this->getTestedObjectPropertyFetch();
            $methodCall->args = [];
            $funcCall->args[] = new \PhpParser\Node\Arg($methodCall);
            $this->nodesToAddCollector->addNodesAfterNode([$assign, $funcCall], $methodCall);
            $this->removeNode($methodCall);
            return;
        }
    }
    private function shouldSkip(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if (!$methodCall->var instanceof \PhpParser\Node\Expr\Variable) {
            return \true;
        }
        if (!$this->nodeNameResolver->isName($methodCall->var, self::THIS)) {
            return \true;
        }
        // skip "createMock" method
        return $this->isName($methodCall->name, 'createMock');
    }
    private function createTestedObjectPropertyFetch(\PhpParser\Node\Stmt\Class_ $class) : \PhpParser\Node\Expr\PropertyFetch
    {
        $propertyName = $this->phpSpecRenaming->resolveObjectPropertyName($class);
        return new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable(self::THIS), $propertyName);
    }
}
