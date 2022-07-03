<?php

declare (strict_types=1);
namespace Rector\Compatibility\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover;
use Rector\Compatibility\NodeAnalyzer\RequiredAnnotationPropertyAnalyzer;
use Rector\Compatibility\NodeFactory\ConstructorClassMethodFactory;
use Rector\Compatibility\ValueObject\PropertyWithPhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\Compatibility\Rector\Class_\AttributeCompatibleAnnotationRector\AttributeCompatibleAnnotationRectorTest
 */
final class AttributeCompatibleAnnotationRector extends AbstractRector
{
    /**
     * @var string
     */
    private const ATTRIBUTE = 'Attribute';
    /**
     * @readonly
     * @var \Rector\Php80\NodeAnalyzer\PhpAttributeAnalyzer
     */
    private $phpAttributeAnalyzer;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocManipulator\PhpDocTagRemover
     */
    private $phpDocTagRemover;
    /**
     * @readonly
     * @var \Rector\Compatibility\NodeAnalyzer\RequiredAnnotationPropertyAnalyzer
     */
    private $requiredAnnotationPropertyAnalyzer;
    /**
     * @readonly
     * @var \Rector\Compatibility\NodeFactory\ConstructorClassMethodFactory
     */
    private $constructorClassMethodFactory;
    public function __construct(PhpAttributeAnalyzer $phpAttributeAnalyzer, PhpDocTagRemover $phpDocTagRemover, RequiredAnnotationPropertyAnalyzer $requiredAnnotationPropertyAnalyzer, ConstructorClassMethodFactory $constructorClassMethodFactory)
    {
        $this->phpAttributeAnalyzer = $phpAttributeAnalyzer;
        $this->phpDocTagRemover = $phpDocTagRemover;
        $this->requiredAnnotationPropertyAnalyzer = $requiredAnnotationPropertyAnalyzer;
        $this->constructorClassMethodFactory = $constructorClassMethodFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change annotation to attribute compatible form, see https://tomasvotruba.com/blog/doctrine-annotations-and-attributes-living-together-in-peace/', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @annotation
 */
class SomeAnnotation
{
    /**
     * @var string[]
     * @Required()
     */
    public array $enum;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor;

/**
 * @annotation
 * @NamedArgumentConstructor
 */
class SomeAnnotation
{
    /**
     * @param string[] $enum
     */
    public function __construct(
        public array $enum
    ) {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNode($node);
        if (!$phpDocInfo instanceof PhpDocInfo) {
            return null;
        }
        if ($this->shouldSkipClass($phpDocInfo, $node)) {
            return null;
        }
        // add "NamedArgumentConstructor"
        $phpDocInfo->addTagValueNode(new DoctrineAnnotationTagValueNode(new IdentifierTypeNode('Doctrine\\Common\\Annotations\\Annotation\\NamedArgumentConstructor')));
        // resolve required properties
        $requiredPropertiesWithPhpDocInfos = [];
        foreach ($node->getProperties() as $property) {
            if (!$property->isPublic()) {
                continue;
            }
            $propertyPhpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            if (!$this->requiredAnnotationPropertyAnalyzer->isRequiredProperty($propertyPhpDocInfo, $property)) {
                continue;
            }
            $propertyName = $this->getName($property);
            $requiredPropertiesWithPhpDocInfos[] = new PropertyWithPhpDocInfo($propertyName, $property, $propertyPhpDocInfo);
        }
        $params = $this->createConstructParams($requiredPropertiesWithPhpDocInfos);
        $constructorClassMethod = $this->constructorClassMethodFactory->createConstructorClassMethod($requiredPropertiesWithPhpDocInfos, $params);
        $node->stmts = \array_merge($node->stmts, [$constructorClassMethod]);
        return $node;
    }
    private function shouldSkipClass(PhpDocInfo $phpDocInfo, Class_ $class) : bool
    {
        if (!$phpDocInfo->hasByNames(['Annotation', 'annotation'])) {
            return \true;
        }
        if ($phpDocInfo->hasByAnnotationClass('Doctrine\\Common\\Annotations\\Annotation\\NamedArgumentConstructor')) {
            return \true;
        }
        // has attribute? skip it
        return $this->phpAttributeAnalyzer->hasPhpAttribute($class, self::ATTRIBUTE);
    }
    /**
     * @param PropertyWithPhpDocInfo[] $requiredPropertiesWithPhpDocInfos
     * @return Param[]
     */
    private function createConstructParams(array $requiredPropertiesWithPhpDocInfos) : array
    {
        $params = [];
        foreach ($requiredPropertiesWithPhpDocInfos as $requiredPropertyWithPhpDocInfo) {
            $property = $requiredPropertyWithPhpDocInfo->getProperty();
            $propertyName = $this->getName($property);
            // unwrap nullable type, as variable is required
            $propertyType = $property->type;
            if ($propertyType instanceof NullableType) {
                $propertyType = $propertyType->type;
            }
            $params[] = new Param(new Variable($propertyName), null, $propertyType, \false, \false, [], $property->flags);
            $propertyPhpDocInfo = $requiredPropertyWithPhpDocInfo->getPhpDocInfo();
            // remove required
            $this->phpDocTagRemover->removeByName($propertyPhpDocInfo, 'Doctrine\\Common\\Annotations\\Annotation\\Required');
            $this->removeNode($property);
        }
        return $params;
    }
}
