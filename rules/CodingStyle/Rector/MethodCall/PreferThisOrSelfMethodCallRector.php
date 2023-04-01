<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\CodingStyle\Enum\PreferenceSelfThis;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202304\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\PreferThisOrSelfMethodCallRectorTest
 */
final class PreferThisOrSelfMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    private const THIS = 'this';
    /**
     * @var array<string, PreferenceSelfThis::*>
     */
    private $typeToPreference = [];
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(AstResolver $astResolver)
    {
        $this->astResolver = $astResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes $this->... and static:: to self:: or vise versa for given types', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    public function run()
    {
        $this->assertEquals('a', 'a');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use PHPUnit\Framework\TestCase;

final class SomeClass extends TestCase
{
    public function run()
    {
        self::assertEquals('a', 'a');
    }
}
CODE_SAMPLE
, ['PHPUnit\\Framework\\TestCase' => PreferenceSelfThis::PREFER_SELF])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        foreach ($this->typeToPreference as $type => $preference) {
            if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType($type))) {
                continue;
            }
            if ($preference === PreferenceSelfThis::PREFER_SELF) {
                return $this->processToSelf($node);
            }
            return $this->processToThis($node);
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allString(\array_keys($configuration));
        Assert::allString($configuration);
        Assert::allOneOf($configuration, [PreferenceSelfThis::PREFER_THIS, PreferenceSelfThis::PREFER_SELF]);
        $this->typeToPreference = $configuration;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function processToSelf($node) : ?StaticCall
    {
        // class is already "self", let's skip it
        if ($node instanceof StaticCall && $this->isName($node->class, ObjectReference::SELF)) {
            return null;
        }
        if ($node instanceof MethodCall && !$this->isName($node->var, self::THIS)) {
            return null;
        }
        $classMethod = $this->astResolver->resolveClassMethodFromCall($node);
        if ($classMethod instanceof ClassMethod && !$classMethod->isStatic()) {
            return null;
        }
        $name = $this->getName($node->name);
        if ($name === null) {
            return null;
        }
        return $this->nodeFactory->createStaticCall(ObjectReference::SELF, $name, $node->args);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function processToThis($node) : ?MethodCall
    {
        if ($node instanceof MethodCall) {
            return null;
        }
        if (!$this->isNames($node->class, [ObjectReference::SELF, ObjectReference::STATIC])) {
            return null;
        }
        $name = $this->getName($node->name);
        if ($name === null) {
            return null;
        }
        // avoid adding dynamic method call to static method
        $classMethod = $this->betterNodeFinder->findParentByTypes($node, [ClassMethod::class]);
        if (!$classMethod instanceof ClassMethod) {
            return $this->nodeFactory->createMethodCall(new Variable(self::THIS), $name, $node->args);
        }
        if (!$classMethod->isStatic()) {
            return $this->nodeFactory->createMethodCall(new Variable(self::THIS), $name, $node->args);
        }
        return null;
    }
}
