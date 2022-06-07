<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PHPStan\Type\ObjectType;
use Rector\BetterPhpDocParser\PhpDocParser\PhpDocFromTypeDeclarationDecorator;
use Rector\Core\Contract\Rector\AllowEmptyConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Transform\Rector\ClassMethod\ReturnTypeWillChangeRector\ReturnTypeWillChangeRectorTest
 */
final class ReturnTypeWillChangeRector extends AbstractRector implements AllowEmptyConfigurableRectorInterface, MinPhpVersionInterface
{
    /**
     * @var array<string, string[]>
     */
    private $classMethodsOfClass = [];
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
    public function __construct(PhpAttributeAnalyzer $phpAttributeAnalyzer, PhpAttributeGroupFactory $phpAttributeGroupFactory)
    {
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->phpAttributeGroupFactory = $phpAttributeGroupFactory;
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
, ['ArrayAccess' => ['offsetGet']])]);
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
        if ($this->phpAttributeAnalyzer->hasPhpAttribute($node, 'ReturnTypeWillChange')) {
            return null;
        }
        if ($node->returnType !== null) {
            return null;
        }
        $classLike = $this->betterNodeFinder->findParentByTypes($node, [Class_::class, Interface_::class]);
        if (!$classLike instanceof ClassLike) {
            return null;
        }
        /** @var array<string, string[]> $classMethodsOfClass */
        $classMethodsOfClass = \array_merge_recursive($this->resolveDefaultConfig(), $this->classMethodsOfClass);
        $className = (string) $this->nodeNameResolver->getName($classLike);
        $objectType = new ObjectType($className);
        $methodName = $this->nodeNameResolver->getName($node);
        $hasChanged = \false;
        foreach ($classMethodsOfClass as $class => $methods) {
            $configuredClassObjectType = new ObjectType($class);
            if (!$configuredClassObjectType->isSuperTypeOf($objectType)->yes()) {
                continue;
            }
            if (!\in_array($methodName, $methods, \true)) {
                continue;
            }
            $attributeGroup = $this->phpAttributeGroupFactory->createFromClass(PhpDocFromTypeDeclarationDecorator::RETURN_TYPE_WILL_CHANGE_ATTRIBUTE);
            $node->attrGroups[] = $attributeGroup;
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
        $this->classMethodsOfClass = $configuration;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersionFeature::RETURN_TYPE_WILL_CHANGE_ATTRIBUTE;
    }
    /**
     * @return array<string, string[]>
     */
    private function resolveDefaultConfig() : array
    {
        $configuration = [];
        foreach (PhpDocFromTypeDeclarationDecorator::ADD_RETURN_TYPE_WILL_CHANGE as $classWithMethods) {
            foreach ($classWithMethods as $class => $methods) {
                $configuration[$class] = \array_merge($configuration[$class] ?? [], $methods);
            }
        }
        return $configuration;
    }
}
