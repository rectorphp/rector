<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Interface_;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Php81\Enum\AttributeName;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\Rector\AbstractRector;
use Rector\Reflection\ReflectionResolver;
use Rector\Transform\ValueObject\ClassMethodReference;
use Rector\ValueObject\PhpVersionFeature;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202407\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\ClassMethod\ReturnTypeWillChangeRector\ReturnTypeWillChangeRectorTest
 */
final class ReturnTypeWillChangeRector extends AbstractRector implements MinPhpVersionInterface, ConfigurableRectorInterface
{
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory
     */
    private $phpAttributeGroupFactory;
    /**
     * @readonly
     * @var \Rector\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    /**
     * @var ClassMethodReference[]
     */
    private $returnTypeChangedClassMethodReferences = [];
    public function __construct(PhpAttributeAnalyzer $phpAttributeAnalyzer, PhpAttributeGroupFactory $phpAttributeGroupFactory, ReflectionResolver $reflectionResolver)
    {
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->reflectionResolver = $reflectionResolver;
        $this->returnTypeChangedClassMethodReferences = [new ClassMethodReference('ArrayAccess', 'getIterator'), new ClassMethodReference('ArrayAccess', 'offsetGet')];
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add #[\\ReturnTypeWillChange] attribute to configured instanceof class with methods', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
class SomeClass implements ArrayAccess
{
    public function offsetGet($offset)
    {
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass implements ArrayAccess
{
    #[\ReturnTypeWillChange]
    public function offsetGet($offset)
    {
    }
}
CODE_SAMPLE
, [new ClassMethodReference('ArrayAccess', 'offsetGet')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class, Interface_::class];
    }
    /**
     * @param Class_|Interface_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        $classReflection = $this->reflectionResolver->resolveClassAndAnonymousClass($node);
        foreach ($node->getMethods() as $classMethod) {
            if ($this->phpAttributeAnalyzer->hasPhpAttribute($classMethod, AttributeName::RETURN_TYPE_WILL_CHANGE)) {
                continue;
            }
            // the return type is known, no need to add attribute
            if ($classMethod->returnType !== null) {
                continue;
            }
            foreach ($this->returnTypeChangedClassMethodReferences as $returnTypeChangedClassMethodReference) {
                if (!$classReflection->isSubclassOf($returnTypeChangedClassMethodReference->getClass())) {
                    continue;
                }
                if (!$this->isName($classMethod, $returnTypeChangedClassMethodReference->getMethod())) {
                    continue;
                }
                $classMethod->attrGroups[] = $this->phpAttributeGroupFactory->createFromClass(AttributeName::RETURN_TYPE_WILL_CHANGE);
                $hasChanged = \true;
                break;
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsInstanceOf($configuration, ClassMethodReference::class);
        $this->returnTypeChangedClassMethodReferences = \array_merge($this->returnTypeChangedClassMethodReferences, $configuration);
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::RETURN_TYPE_WILL_CHANGE_ATTRIBUTE;
    }
}
