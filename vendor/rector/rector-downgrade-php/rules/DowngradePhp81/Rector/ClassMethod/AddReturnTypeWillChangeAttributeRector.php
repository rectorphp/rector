<?php

declare (strict_types=1);
namespace Rector\DowngradePhp81\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\AttributeGroup;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://php.watch/versions/8.1/ReturnTypeWillChange
 *
 * @see \Rector\Tests\DowngradePhp81\Rector\ClassMethod\AddReturnTypeWillChangeAttributeRector\AddReturnTypeWillChangeAttributeRectorTest
 */
final class AddReturnTypeWillChangeAttributeRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @readonly
     */
    private PhpAttributeAnalyzer $phpAttributeAnalyzer;
    /**
     * @var array<string, string[]>
     *
     * PHP 8.1 added provisional return types to these core interface methods.
     * Implementations without matching return types trigger deprecation notices
     * on PHP 8.1+. Adding #[\ReturnTypeWillChange] suppresses those notices,
     * keeping the code compatible with older PHP versions simultaneously.
     */
    private const INTERFACE_METHOD_MAP = ['ArrayAccess' => ['offsetGet', 'offsetExists', 'offsetSet', 'offsetUnset'], 'Countable' => ['count'], 'Iterator' => ['current', 'key', 'next', 'rewind', 'valid'], 'IteratorAggregate' => ['getIterator'], 'Stringable' => ['__toString']];
    /**
     * @var string
     */
    private const RETURN_TYPE_WILL_CHANGE = 'ReturnTypeWillChange';
    public function __construct(ReflectionProvider $reflectionProvider, PhpAttributeAnalyzer $phpAttributeAnalyzer)
    {
        $this->reflectionProvider = $reflectionProvider;
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add #[\ReturnTypeWillChange] attribute to methods implementing PHP 8.1 interface methods with provisional return types, to suppress deprecation notices when running on PHP 8.1+ without the required return types', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass implements \ArrayAccess
{
    public function offsetGet($offset)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass implements \ArrayAccess
{
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
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
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $className = $this->getName($node);
        if ($className === null) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass($className)) {
            return null;
        }
        $classReflection = $this->reflectionProvider->getClass($className);
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            if ($this->phpAttributeAnalyzer->hasPhpAttribute($classMethod, self::RETURN_TYPE_WILL_CHANGE)) {
                continue;
            }
            if ($classMethod->returnType instanceof Node) {
                continue;
            }
            $methodName = $this->getName($classMethod);
            foreach (self::INTERFACE_METHOD_MAP as $interface => $methods) {
                if (!in_array($methodName, $methods, \true)) {
                    continue;
                }
                if (!$classReflection->is($interface)) {
                    continue;
                }
                $classMethod->attrGroups[] = new AttributeGroup([new Attribute(new FullyQualified(self::RETURN_TYPE_WILL_CHANGE))]);
                $hasChanged = \true;
                continue 2;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
