<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
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
final class MakeEntitySetterNullabilityInSyncWithPropertyRector extends \Rector\Core\Rector\AbstractRector
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
    public function __construct(\Rector\Doctrine\NodeAnalyzer\SetterClassMethodAnalyzer $setterClassMethodAnalyzer, \Rector\Doctrine\PhpDocParser\DoctrineDocBlockResolver $doctrineDocBlockResolver)
    {
        $this->setterClassMethodAnalyzer = $setterClassMethodAnalyzer;
        $this->doctrineDocBlockResolver = $doctrineDocBlockResolver;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Make nullability in setter class method with respect to property', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Product
{
    /**
     * @ORM\ManyToOne(targetEntity="AnotherEntity")
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        // is setter in doctrine?
        if (!$this->doctrineDocBlockResolver->isInDoctrineEntityClass($node)) {
            return null;
        }
        $property = $this->setterClassMethodAnalyzer->matchNullalbeClassMethodProperty($node);
        if (!$property instanceof \PhpParser\Node\Stmt\Property) {
            return null;
        }
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($property);
        if (!$phpDocInfo->hasByAnnotationClass('Doctrine\\ORM\\Mapping\\ManyToOne')) {
            return null;
        }
        $param = $node->params[0];
        /** @var NullableType $paramType */
        $paramType = $param->type;
        $param->type = $paramType->type;
        return $node;
    }
}
