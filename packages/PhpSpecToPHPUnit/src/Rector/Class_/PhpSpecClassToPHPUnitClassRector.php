<?php declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Clone_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
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

    private function processLetMethod(ClassMethod $classMethod): void
    {
        $classMethod->name = new Identifier('setUp');

        $this->makeProtected($classMethod);

        // "beConstructedWith()" → "$this->{testedObject} = new ..."
        $this->callableNodeTraverser->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if (! $this->isName($node, 'beConstructedWith')) {
                return null;
            }

            $new = new New_(new FullyQualified($this->testedClass));
            $new->args = $node->args;

            return new Assign($this->testedObjectPropertyFetch, $new);
        });
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
        $methods = $this->classManipulator->getMethodsByName($class);

        // let → setUp
        foreach ($methods as $name => $method) {
            if ($name === 'let') {
                $this->processLetMethod($method);
            } else {
                /** @var string $name */
                $this->processTestMethod($method, $name);
            }
        }
    }

    private function processTestMethod(ClassMethod $classMethod, string $name): void
    {
        // change name to phpunit test case format
        $this->phpSpecRenaming->renameMethod($classMethod, $name);

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

        $this->removeNode($nextCall);

        $methodName = $this->getValue($nextCall->args[0]->value);

        $separatedCall = new MethodCall($this->testedObjectPropertyFetch, $methodName);
        $separatedCall->args[] = $nextCall->args[1];

        $this->addNodeAfterNode($expectException, $methodCall);
        $this->addNodeAfterNode($separatedCall, $methodCall);
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
