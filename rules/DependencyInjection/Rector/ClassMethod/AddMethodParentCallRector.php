<?php

declare (strict_types=1);
namespace Rector\DependencyInjection\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Enum\ObjectReference;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202304\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\DependencyInjection\Rector\ClassMethod\AddMethodParentCallRector\AddMethodParentCallRectorTest
 */
final class AddMethodParentCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var array<string, string>
     */
    private $methodByParentTypes = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add method parent call, in case new parent method is added', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SunshineCommand extends ParentClassWithNewConstructor
{
    public function __construct()
    {
        $value = 5;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SunshineCommand extends ParentClassWithNewConstructor
{
    public function __construct()
    {
        $value = 5;

        parent::__construct();
    }
}
CODE_SAMPLE
, ['ParentClassWithNewConstructor' => MethodName::CONSTRUCT])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        $classLike = $this->betterNodeFinder->findParentType($node, ClassLike::class);
        if (!$classLike instanceof ClassLike) {
            return null;
        }
        $className = (string) $this->nodeNameResolver->getName($classLike);
        foreach ($this->methodByParentTypes as $type => $method) {
            // not itself
            if ($className === $type) {
                continue;
            }
            if ($this->shouldSkipMethod($node, $method)) {
                continue;
            }
            if (!$this->isObjectType($classLike, new ObjectType($type))) {
                continue;
            }
            $node->stmts[] = $this->createParentStaticCall($method);
            return $node;
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
        /** @var array<string, string> $configuration */
        $this->methodByParentTypes = $configuration;
    }
    private function shouldSkipMethod(ClassMethod $classMethod, string $method) : bool
    {
        if (!$this->isName($classMethod, $method)) {
            return \true;
        }
        return $this->hasParentCallOfMethod($classMethod, $method);
    }
    private function createParentStaticCall(string $method) : Expression
    {
        $staticCall = $this->nodeFactory->createStaticCall(ObjectReference::PARENT, $method);
        return new Expression($staticCall);
    }
    /**
     * Looks for "parent::<methodName>
     */
    private function hasParentCallOfMethod(ClassMethod $classMethod, string $method) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (Node $node) use($method) : bool {
            if (!$node instanceof StaticCall) {
                return \false;
            }
            if (!$this->isName($node->class, ObjectReference::PARENT)) {
                return \false;
            }
            return $this->isName($node->name, $method);
        });
    }
}
