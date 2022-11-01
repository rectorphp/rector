<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use Rector\Core\Contract\Rector\AllowEmptyConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Reflection\ReflectionResolver;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\Php81\Enum\AttributeName;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\Transform\ValueObject\ClassMethodReference;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202211\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\ClassMethod\ReturnTypeWillChangeRector\ReturnTypeWillChangeRectorTest
 */
final class ReturnTypeWillChangeRector extends AbstractRector implements AllowEmptyConfigurableRectorInterface, MinPhpVersionInterface
{
    /**
     * @var ClassMethodReference[]
     */
    private $returnTypeChangedClassMethodReferences = [];
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
     * @var \Rector\Core\Reflection\ReflectionResolver
     */
    private $reflectionResolver;
    public function __construct(PhpAttributeAnalyzer $phpAttributeAnalyzer, PhpAttributeGroupFactory $phpAttributeGroupFactory, ReflectionResolver $reflectionResolver)
    {
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
        $this->reflectionResolver = $reflectionResolver;
        $this->returnTypeChangedClassMethodReferences[] = new ClassMethodReference('ArrayAccess', 'getIterator');
        $this->returnTypeChangedClassMethodReferences[] = new ClassMethodReference('ArrayAccess', 'offsetGet');
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->phpAttributeAnalyzer->hasPhpAttribute($node, AttributeName::RETURN_TYPE_WILL_CHANGE)) {
            return null;
        }
        // the return type is known, no need to add attribute
        if ($node->returnType !== null) {
            return null;
        }
        $classLike = $this->betterNodeFinder->findParentByTypes($node, [Class_::class, Interface_::class]);
        if (!$classLike instanceof ClassLike) {
            return null;
        }
        $classReflection = $this->reflectionResolver->resolveClassAndAnonymousClass($classLike);
        $methodName = $node->name->toString();
        $hasChanged = \false;
        foreach ($this->returnTypeChangedClassMethodReferences as $returnTypeChangedClassMethodReference) {
            if (!$classReflection->isSubclassOf($returnTypeChangedClassMethodReference->getClass())) {
                continue;
            }
            if ($returnTypeChangedClassMethodReference->getMethod() !== $methodName) {
                continue;
            }
            $node->attrGroups[] = $this->phpAttributeGroupFactory->createFromClass(AttributeName::RETURN_TYPE_WILL_CHANGE);
            $hasChanged = \true;
            break;
        }
        if (!$hasChanged) {
            return null;
        }
        return $node;
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
