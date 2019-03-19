<?php declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\PhpParser\Node\VariableInfo;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Util\RectorStrings;

/**
 * @see https://gnugat.github.io/2015/09/23/phpunit-with-phpspec.html
 */
final class PhpSpecClassToPHPUnitClassRector extends AbstractRector
{
    /**
     * @var string
     */
    private $objectBehaviorClass;

    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var CallableNodeTraverser
     */
    private $callableNodeTraverser;

    /**
     * @var string
     */
    private $testedClass;

    /**
     * @var PropertyFetch
     */
    private $testedObjectPropertyFetch;

    /**
     * @var string[][]
     */
    private $newMethodToOldMethods = [
        'assertInstanceOf' => ['shouldBeAnInstanceOf', 'shouldHaveType', 'shouldReturnAnInstanceOf'],
        'assertSame' => ['shouldBe', 'shouldReturn', 'willReturn'],
        'assertNotSame' => ['shouldNotBe', 'shouldNotReturn', 'willNotReturn'],
        'assertCount' => ['shouldHaveCount'],
        'assertEquals' => ['shouldBeEqualTo'],
        'assertNotEquals' => ['shouldNotBeEqualTo'],
    ];

    /**
     * @var bool
     */
    private $isBoolAssert = false;

    /**
     * @var PhpSpecRenaming
     */
    private $phpSpecRenaming;

    public function __construct(
        ClassManipulator $classManipulator,
        CallableNodeTraverser $callableNodeTraverser,
        PhpSpecRenaming $phpSpecRenaming,
        string $objectBehaviorClass = 'PhpSpec\ObjectBehavior'
    ) {
        $this->classManipulator = $classManipulator;
        $this->callableNodeTraverser = $callableNodeTraverser;
        $this->phpSpecRenaming = $phpSpecRenaming;
        $this->objectBehaviorClass = $objectBehaviorClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Migrate PhpSpec object behavior spec to PHPUnit test case', [
            new CodeSample(
                <<<'CODE_SAMPLE'
namespace spec\SomeNamespaceForThisTest;

use PhpSpec\ObjectBehavior;

class CartSpec extends ObjectBehavior
{
    public function let()
    {
        $this->beConstructedWith(5);
    }

    public function it_returns_id()
    {
        $this->id()->shouldReturn(5);
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
namespace SomeNamespaceForThisTest;

use PhpSpec\ObjectBehavior;

class CartTest extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        $this->cart = new Cart(5);
    }

    public function testReturnsId()
    {
        $this->assertSame(5, $this->cart->id());
    }
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
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
        // anonymous class
        if ($node->name === null) {
            return null;
        }

        if (! $this->isType($node, $this->objectBehaviorClass)) {
            return null;
        }

        // 1. change namespace name to PHPUnit-like
        $this->phpSpecRenaming->renameNamespace($node);

        $propertyName = $this->phpSpecRenaming->resolveObjectPropertyName($node);

        $this->phpSpecRenaming->renameClass($node);
        $this->phpSpecRenaming->renameExtends($node);

        $this->testedClass = $this->resolveTestedClass($node);

        // used later in many places
        $this->testedObjectPropertyFetch = new PropertyFetch(new Variable('this'), $propertyName);
        $this->classManipulator->addPropertyToClass($node, new VariableInfo($propertyName, $this->testedClass));

        $this->processMethods($node);

        return $node;
    }

    private function processBeConstructed(ClassMethod $classMethod): void
    {
        if ($this->isName($classMethod, 'let')) {
            $classMethod->name = new Identifier('setUp');
            $this->makeProtected($classMethod);
        }

        // remove params and turn them to instances
        $assigns = [];
        if ($classMethod->params) {
            foreach ($classMethod->params as $param) {
                if (! $param->type instanceof Name) {
                    throw new ShouldNotHappenException();
                }

                $methodCall = new MethodCall(new Variable('this'), 'createMock');
                $methodCall->args[] = new Arg(new ClassConstFetch(new FullyQualified($param->type), 'class'));

                $varDoc = sprintf(
                    '/** @var \%s|\%s $%s */',
                    $this->getName($param->type),
                    'PHPUnit\Framework\MockObject\MockObject',
                    $this->getName($param->var)
                );

                $assign = new Assign($param->var, $methodCall);

                // add @var doc comment
                $assignExpression = new Expression($assign);
                $assignExpression->setDocComment(new Doc($varDoc));

                $assigns[] = $assignExpression;
            }

            $classMethod->params = [];
        }

        // "beConstructedWith()" → "$this->{testedObject} = new ..."
        $this->callableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if (! $this->isName($node, 'beConstructed*')) {
                return null;
            }

            if ($this->isName($node, 'beConstructedWith')) {
                $new = new New_(new FullyQualified($this->testedClass));
                $new->args = $node->args;

                return new Assign($this->testedObjectPropertyFetch, $new);
            } elseif ($this->isName($node, 'beConstructedThrough')) {
                // static method
                $methodName = $this->getValue($node->args[0]->value);
                $staticCall = new StaticCall(new FullyQualified($this->testedClass), $methodName);

                if (isset($node->args[1])) {
                    $staticCall->args[] = $node->args[1];
                }

                return new Assign($this->testedObjectPropertyFetch, $staticCall);
            }

            return null;
        });

        if ($assigns) {
            $classMethod->stmts = array_merge($assigns, (array) $classMethod->stmts);
        }
    }

    private function thisToTestedObjectPropertyFetch(Expr $expr): Expr
    {
        if (! $expr instanceof Variable) {
            return $expr;
        }

        if (! $this->isName($expr, 'this')) {
            return $expr;
        }

        return $this->testedObjectPropertyFetch;
    }

    private function createAssertMethod(string $name, Expr $expected, Expr $value): MethodCall
    {
        $this->isBoolAssert = false;
        // special case with bool!
        $name = $this->resolveBoolMethodName($name, $expected);

        $assetMethodCall = new MethodCall(new Variable('this'), new Identifier($name));

        if ($this->isBoolAssert === false) {
            $assetMethodCall->args[] = new Arg($this->thisToTestedObjectPropertyFetch($expected));
        }

        $assetMethodCall->args[] = new Arg($this->thisToTestedObjectPropertyFetch($value));

        return $assetMethodCall;
    }

    private function processMethods(Class_ $class): void
    {
        // let → setUp
        foreach ($this->classManipulator->getMethods($class) as $method) {
            if ($this->isName($method, 'let')) {
                $this->processBeConstructed($method);
            } else {
                /** @var string $name */
                $this->processBeConstructed($method);
                $this->processTestMethod($method);
            }
        }
    }

    private function processTestMethod(ClassMethod $classMethod): void
    {
        // change name to phpunit test case format
        $this->phpSpecRenaming->renameMethod($classMethod);

        // replace "$this" with "$this->{testedObject}"
        $this->callableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) {
            // $this ↓
            // $this->testedObject
            if (! $node instanceof MethodCall) {
                return null;
            }

            if ($this->isName($node, 'shouldBeCalled')) {
                // chain call - use method that compares something
                if ($node->getAttribute(Attribute::PARENT_NODE) instanceof MethodCall) {
                    /** @var MethodCall $node */
                    $node = $node->var;
                // last call, no added value
                } else {
                    $this->removeNode($node);
                    return null;
                }
            }

            foreach ($this->newMethodToOldMethods as $newMethod => $oldMethods) {
                if ($this->isNames($node, $oldMethods)) {
                    return $this->createAssertMethod($newMethod, $node->args[0]->value, $node->var);
                }
            }

            if ($this->isName($node->name, 'shouldThrow')) {
                $this->processShouldThrow($node);
                return null;
            }

            if ($node->var instanceof Variable && $this->isName($node->var, 'this')) {
                // skip "createMock" method
                if ($this->isName($node, 'createMock')) {
                    return $node;
                }

                // $this->clone() ↓
                // clone $this->testedObject
                if ($this->isName($node, 'clone')) {
                    return new Clone_($this->testedObjectPropertyFetch);
                }

                $node->var = $this->testedObjectPropertyFetch;

                return $node;
            }

            return null;
        });
    }

    private function resolveTestedClass(Node $node): string
    {
        /** @var string $className */
        $className = $node->getAttribute(Attribute::CLASS_NAME);

        $newClassName = RectorStrings::removePrefixes($className, ['spec\\']);

        return RectorStrings::removeSuffixes($newClassName, ['Spec']);
    }

    private function processShouldThrow(MethodCall $methodCall): void
    {
        $expectException = new MethodCall(new Variable('this'), 'expectException');
        $expectException->args[] = $methodCall->args[0];

        $nextCall = $methodCall->getAttribute(Attribute::PARENT_NODE);
        if (! $nextCall instanceof MethodCall) {
            return;
        }

        if ($this->isName($nextCall, 'duringInstantiation')) {
            // expected instantiation
            /** @var Expression $previousExpression */
            $previousExpression = $methodCall->getAttribute(Attribute::PREVIOUS_EXPRESSION);

            /** @var Expression $previousPreviousExpression */
            $previousPreviousExpression = $previousExpression->getAttribute(Attribute::PREVIOUS_EXPRESSION);

            $this->addNodeAfterNode($expectException, $previousPreviousExpression);
            $this->removeNode($nextCall);
        } else {
            if (! isset($nextCall->args[0])) {
                return;
            }

            $methodName = $this->getValue($nextCall->args[0]->value);

            $this->removeNode($nextCall);

            $separatedCall = new MethodCall($this->testedObjectPropertyFetch, $methodName);

            $this->addNodeAfterNode($expectException, $methodCall);

            if (! isset($nextCall->args[1])) {
                return;
            }

            $separatedCall->args[] = $nextCall->args[1];
            $this->addNodeAfterNode($separatedCall, $methodCall);
        }
    }

    private function resolveBoolMethodName(string $name, Expr $expr): string
    {
        if (! $this->isBool($expr)) {
            return $name;
        }

        if ($name === 'assertSame') {
            $this->isBoolAssert = true;
            return $this->isFalse($expr) ? 'assertFalse' : 'assertTrue';
        }

        if ($name === 'assertNotSame') {
            $this->isBoolAssert = true;
            return $this->isFalse($expr) ? 'assertNotFalse' : 'assertNotTrue';
        }

        return $name;
    }
}
