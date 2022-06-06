<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Transform\Rector\ClassMethod;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Stmt\Class_;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassLike;
use RectorPrefix20220606\PhpParser\Node\Stmt\ClassMethod;
use RectorPrefix20220606\PhpParser\Node\Stmt\Interface_;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocParser\PhpDocFromTypeDeclarationDecorator;
use RectorPrefix20220606\Rector\Core\Contract\Rector\AllowEmptyConfigurableRectorInterface;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\Core\ValueObject\PhpVersionFeature;
use RectorPrefix20220606\Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use RectorPrefix20220606\Rector\PhpAttribute\NodeFactory\PhpAttributeGroupFactory;
use RectorPrefix20220606\Rector\VersionBonding\Contract\MinPhpVersionInterface;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
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
