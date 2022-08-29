<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\ComplexType;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode;
use Rector\BetterPhpDocParser\PhpDoc\ArrayItemNode;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Doctrine\NodeAnalyzer\SetterClassMethodAnalyzer;
use Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see related to maker bundle https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
 *
 * @see \Rector\Doctrine\Tests\Rector\ClassMethod\MakeEntitySetterNullabilityInSyncWithPropertyRector\MakeEntitySetterNullabilityInSyncWithPropertyRectorTest
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
    public function __construct(SetterClassMethodAnalyzer $setterClassMethodAnalyzer, DoctrineDocBlockResolver $doctrineDocBlockResolver)
    {
        $this->setterClassMethodAnalyzer = $setterClassMethodAnalyzer;
        $this->doctrineDocBlockResolver = $doctrineDocBlockResolver;
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
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node) : ?Node
    {
        // is setter in doctrine?
        if (!$this->doctrineDocBlockResolver->isInDoctrineEntityClass($node)) {
            return null;
        }
        $property = $this->setterClassMethodAnalyzer->matchNullalbeClassMethodProperty($node);
        if (!$property instanceof Property) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        $manyToOneAnnotationTagValueNode = $phpDocInfo->getByAnnotationClass('Doctrine\\ORM\\Mapping\\ManyToOne');
        if (!$manyToOneAnnotationTagValueNode instanceof DoctrineAnnotationTagValueNode) {
            return null;
        }
        $param = $node->params[0];
        $paramType = $param->type;
        if (!$this->isJoinColumnNullable($phpDocInfo)) {
            // remove nullable if has one
            if (!$paramType instanceof NullableType) {
                return null;
            }
            $param->type = $paramType->type;
            return $node;
        }
        // already nullable, lets skip it
        if ($paramType instanceof NullableType) {
            return null;
        }
        // we skip complex type as multiple or nullable already
        if ($paramType instanceof ComplexType) {
            return null;
        }
        // no type at all, there is nothing we can do
        if (!$paramType instanceof Node) {
            return null;
        }
        $param->type = new NullableType($paramType);
        return $node;
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
