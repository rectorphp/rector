<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Core\Rector\AbstractRector;
use Rector\Doctrine\NodeAnalyzer\SetterClassMethodAnalyzer;
use Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see related to maker bundle https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
 *
 * @see \Rector\Doctrine\Tests\CodeQuality\Rector\ClassMethod\MakeEntitySetterNullabilityInSyncWithPropertyRector\MakeEntitySetterNullabilityInSyncWithPropertyRectorTest
 */
final class MakeEntitySetterNullabilityInSyncWithPropertyRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Doctrine\NodeAnalyzer\SetterClassMethodAnalyzer
     */
    private $setterClassMethodAnalyzer;
    /**
     * @readonly
     * @var \Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver
     */
    private $doctrineDocBlockResolver;
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(SetterClassMethodAnalyzer $setterClassMethodAnalyzer, DoctrineDocBlockResolver $doctrineDocBlockResolver, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->setterClassMethodAnalyzer = $setterClassMethodAnalyzer;
        $this->doctrineDocBlockResolver = $doctrineDocBlockResolver;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Make nullability in setter class method with respect to property', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Product
{
    /**
     * @ORM\ManyToOne(targetEntity="AnotherEntity")
     * @ORM\JoinColumn(nullable=false)
     */
    private $anotherEntity;

    public function setAnotherEntity(?AnotherEntity $anotherEntity)
    {
        $this->anotherEntity = $anotherEntity;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Product
{
    /**
     * @ORM\ManyToOne(targetEntity="AnotherEntity")
     * @ORM\JoinColumn(nullable=false)
     */
    private $anotherEntity;

    public function setAnotherEntity(AnotherEntity $anotherEntity)
    {
        $this->anotherEntity = $anotherEntity;
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
        // is setter in doctrine?
        if (!$this->doctrineDocBlockResolver->isDoctrineEntityClass($node)) {
            return null;
        }
        $hasChanged = \false;
        foreach ($node->getMethods() as $classMethod) {
            $propertyName = $this->setterClassMethodAnalyzer->matchNullalbeClassMethodPropertyName($classMethod);
            if ($propertyName === null) {
                continue;
            }
            $property = $node->getProperty($propertyName);
            if (!$property instanceof Property) {
                continue;
            }
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
            $doctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass('Doctrine\\ORM\\Mapping\\ManyToOne');
            if (!$doctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
                continue;
            }
            $param = $classMethod->params[0];
            $paramType = $param->type;
            if (!$this->isJoinColumnNullable($phpDocInfo)) {
                // remove nullable if has one
                if (!$paramType instanceof NullableType) {
                    continue;
                }
                $param->type = $paramType->type;
                $hasChanged = \true;
                continue;
            }
            // already nullable, lets skip it
            if ($paramType instanceof NullableType) {
                continue;
            }
            // we skip complex type as multiple or nullable already
            if ($paramType instanceof ComplexType) {
                continue;
            }
            // no type at all, there is nothing we can do
            if (!$paramType instanceof Node) {
                continue;
            }
            $param->type = new NullableType($paramType);
            $hasChanged = \true;
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
    private function isJoinColumnNullable(PhpDocInfo $phpDocInfo) : bool
    {
        $joinColumnDoctrineAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass('Doctrine\\ORM\\Mapping\\JoinColumn');
        if (!$joinColumnDoctrineAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            // no join column means the join is nullable
            return \true;
        }
        $arrayItemNode = $joinColumnDoctrineAnnotationTagValueNode->getValue('nullable');
        if (!$arrayItemNode instanceof ArrayItemNode) {
            return \true;
        }
        return !$arrayItemNode->value instanceof ConstExprFalseNode;
    }
}
