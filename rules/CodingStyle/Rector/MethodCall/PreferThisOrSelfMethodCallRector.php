<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\CodingStyle\Enum\PreferenceSelfThis;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220501\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\CodingStyle\Rector\MethodCall\PreferThisOrSelfMethodCallRector\PreferThisOrSelfMethodCallRectorTest
 */
final class PreferThisOrSelfMethodCallRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    private const THIS = 'this';
    /**
     * @var array<PreferenceSelfThis>
     */
    private $typeToPreference = [];
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(\Rector\Core\PhpParser\AstResolver $astResolver)
    {
        $this->astResolver = $astResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes $this->... and static:: to self:: or vise versa for given types', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, ['PHPUnit\\Framework\\TestCase' => \Rector\CodingStyle\Enum\PreferenceSelfThis::PREFER_SELF()])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        foreach ($this->typeToPreference as $type => $preference) {
            if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType($type))) {
                continue;
            }
            /** @var PreferenceSelfThis $preference */
            if ($preference->equals(\Rector\CodingStyle\Enum\PreferenceSelfThis::PREFER_SELF())) {
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
        \RectorPrefix20220501\Webmozart\Assert\Assert::allString(\array_keys($configuration));
        \RectorPrefix20220501\Webmozart\Assert\Assert::allIsAOf($configuration, \Rector\CodingStyle\Enum\PreferenceSelfThis::class);
        $this->typeToPreference = $configuration;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function processToSelf($node) : ?\PhpParser\Node\Expr\StaticCall
    {
        if ($node instanceof \PhpParser\Node\Expr\StaticCall && !$this->isNames($node->class, [\Rector\Core\Enum\ObjectReference::SELF()->getValue(), \Rector\Core\Enum\ObjectReference::STATIC()->getValue()])) {
            return null;
        }
        if ($node instanceof \PhpParser\Node\Expr\MethodCall && !$this->isName($node->var, self::THIS)) {
            return null;
        }
        $classMethod = $this->astResolver->resolveClassMethodFromCall($node);
        if ($classMethod instanceof \PhpParser\Node\Stmt\ClassMethod && !$classMethod->isStatic()) {
            return null;
        }
        $name = $this->getName($node->name);
        if ($name === null) {
            return null;
        }
        return $this->nodeFactory->createStaticCall(\Rector\Core\Enum\ObjectReference::SELF(), $name, $node->args);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     */
    private function processToThis($node) : ?\PhpParser\Node\Expr\MethodCall
    {
        if ($node instanceof \PhpParser\Node\Expr\MethodCall) {
            return null;
        }
        if (!$this->isNames($node->class, [\Rector\Core\Enum\ObjectReference::SELF()->getValue(), \Rector\Core\Enum\ObjectReference::STATIC()->getValue()])) {
            return null;
        }
        $name = $this->getName($node->name);
        if ($name === null) {
            return null;
        }
        // avoid adding dynamic method call to static method
        $classMethod = $this->betterNodeFinder->findParentByTypes($node, [\PhpParser\Node\Stmt\ClassMethod::class]);
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return $this->nodeFactory->createMethodCall(self::THIS, $name, $node->args);
        }
        if (!$classMethod->isStatic()) {
            return $this->nodeFactory->createMethodCall(self::THIS, $name, $node->args);
        }
        return null;
    }
}
