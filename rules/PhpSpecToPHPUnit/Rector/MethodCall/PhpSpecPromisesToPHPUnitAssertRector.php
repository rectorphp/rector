<?php

declare(strict_types=1);

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
final class PhpSpecPromisesToPHPUnitAssertRector extends AbstractPhpSpecToPHPUnitRector
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

    private ?string $testedClass = null;

    private bool $isPrepared = false;

    /**
     * @var string[]
     */
    private array $matchersKeys = [];

    private ?PropertyFetch $testedObjectPropertyFetch = null;

    public function __construct(
        private MatchersManipulator $matchersManipulator,
        private PhpSpecRenaming $phpSpecRenaming,
        private AssertMethodCallFactory $assertMethodCallFactory,
        private BeConstructedWithAssignFactory $beConstructedWithAssignFactory,
        private DuringMethodCallFactory $duringMethodCallFactory
    ) {
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
        $this->isPrepared = false;
        $this->matchersKeys = [];

        if (! $this->isInPhpSpecBehavior($node)) {
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
            return $this->beConstructedWithAssignFactory->create(
                $node,
                $this->getTestedClass(),
                $this->getTestedObjectPropertyFetch()
            );
        }

        $this->processMatchersKeys($node);

        foreach (self::NEW_METHOD_TO_OLD_METHODS as $newMethod => $oldMethods) {
            if (! $this->isNames($node->name, $oldMethods)) {
                continue;
            }

            return $this->assertMethodCallFactory->createAssertMethod(
                $newMethod,
                $node->var,
                $node->args[0]->value ?? null,
                $this->getTestedObjectPropertyFetch()
            );
        }

        if ($this->shouldSkip($node)) {
            return null;
        }

        if ($this->isName($node->name, 'clone')) {
            return new Clone_($this->getTestedObjectPropertyFetch());
        }

        $methodName = $this->getName($node->name);
        if ($methodName === null) {
            return null;
        }

        /** @var Class_ $classLike */
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        $classMethod = $classLike->getMethod($methodName);
        // it's a method call, skip
        if ($classMethod !== null) {
            return null;
        }

        $node->var = $this->getTestedObjectPropertyFetch();

        return $node;
    }

    private function processDuringInstantiation(MethodCall $methodCall): MethodCall
    {
        /** @var MethodCall $parentMethodCall */
        $parentMethodCall = $methodCall->var;
        $parentMethodCall->name = new Identifier('expectException');

        return $parentMethodCall;
    }

    private function prepareMethodCall(MethodCall $methodCall): void
    {
        if ($this->isPrepared) {
            return;
        }

        /** @var Class_ $classLike */
        $classLike = $methodCall->getAttribute(AttributeKey::CLASS_NODE);

        $this->matchersKeys = $this->matchersManipulator->resolveMatcherNamesFromClass($classLike);
        $this->testedClass = $this->phpSpecRenaming->resolveTestedClass($methodCall);
        $this->testedObjectPropertyFetch = $this->createTestedObjectPropertyFetch($classLike);

        $this->isPrepared = true;
    }

    private function getTestedObjectPropertyFetch(): PropertyFetch
    {
        if ($this->testedObjectPropertyFetch === null) {
            throw new ShouldNotHappenException();
        }

        return $this->testedObjectPropertyFetch;
    }

    private function getTestedClass(): string
    {
        if ($this->testedClass === null) {
            throw new ShouldNotHappenException();
        }

        return $this->testedClass;
    }

    /**
     * @changelog https://johannespichler.com/writing-custom-phpspec-matchers/
     */
    private function processMatchersKeys(MethodCall $methodCall): void
    {
        foreach ($this->matchersKeys as $matcherKey) {
            if (! $this->isName($methodCall->name, 'should' . ucfirst($matcherKey))) {
                continue;
            }

            if (! $methodCall->var instanceof MethodCall) {
                continue;
            }

            // 1. assign callable to variable
            $thisGetMatchers = $this->nodeFactory->createMethodCall(self::THIS, 'getMatchers');
            $arrayDimFetch = new ArrayDimFetch($thisGetMatchers, new String_($matcherKey));
            $matcherCallableVariable = new Variable('matcherCallable');
            $assign = new Assign($matcherCallableVariable, $arrayDimFetch);

            // 2. call it on result
            $funcCall = new FuncCall($matcherCallableVariable);
            $funcCall->args = $methodCall->args;

            $methodCall->name = $methodCall->var->name;
            $methodCall->var = $this->getTestedObjectPropertyFetch();
            $methodCall->args = [];
            $funcCall->args[] = new Arg($methodCall);

            $this->addNodesAfterNode([$assign, $funcCall], $methodCall);

            $this->removeNode($methodCall);

            return;
        }
    }

    private function shouldSkip(MethodCall $methodCall): bool
    {
        if (! $methodCall->var instanceof Variable) {
            return true;
        }

        if (! $this->nodeNameResolver->isName($methodCall->var, self::THIS)) {
            return true;
        }

        // skip "createMock" method
        return $this->isName($methodCall->name, 'createMock');
    }

    private function createTestedObjectPropertyFetch(Class_ $class): PropertyFetch
    {
        $propertyName = $this->phpSpecRenaming->resolveObjectPropertyName($class);

        return new PropertyFetch(new Variable(self::THIS), $propertyName);
    }
}
