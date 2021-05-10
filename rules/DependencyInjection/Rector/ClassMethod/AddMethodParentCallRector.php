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
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DependencyInjection\Rector\ClassMethod\AddMethodParentCallRector\AddMethodParentCallRectorTest
 */
final class AddMethodParentCallRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHODS_BY_PARENT_TYPES = 'methods_by_parent_type';
    /**
     * @var array<string, string>
     */
    private $methodByParentTypes = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Add method parent call, in case new parent method is added', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
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
, [self::METHODS_BY_PARENT_TYPES => ['ParentClassWithNewConstructor' => \Rector\Core\ValueObject\MethodName::CONSTRUCT]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $classLike = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return null;
        }
        /** @var string $className */
        $className = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        foreach ($this->methodByParentTypes as $type => $method) {
            if (!$this->isObjectType($classLike, new \PHPStan\Type\ObjectType($type))) {
                continue;
            }
            // not itself
            if ($className === $type) {
                continue;
            }
            if ($this->shouldSkipMethod($node, $method)) {
                continue;
            }
            $node->stmts[] = $this->createParentStaticCall($method);
            return $node;
        }
        return null;
    }
    /**
     * @param array<string, array<string, string>> $configuration
     */
    public function configure(array $configuration) : void
    {
        $this->methodByParentTypes = $configuration[self::METHODS_BY_PARENT_TYPES] ?? [];
    }
    private function shouldSkipMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $method) : bool
    {
        if (!$this->isName($classMethod, $method)) {
            return \true;
        }
        return $this->hasParentCallOfMethod($classMethod, $method);
    }
    private function createParentStaticCall(string $method) : \PhpParser\Node\Stmt\Expression
    {
        $staticCall = $this->nodeFactory->createStaticCall('parent', $method);
        return new \PhpParser\Node\Stmt\Expression($staticCall);
    }
    /**
     * Looks for "parent::<methodName>
     */
    private function hasParentCallOfMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, string $method) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst((array) $classMethod->stmts, function (\PhpParser\Node $node) use($method) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\StaticCall) {
                return \false;
            }
            if (!$this->isName($node->class, 'parent')) {
                return \false;
            }
            return $this->isName($node->name, $method);
        });
    }
}
