<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\ManyToOneTagValueNode;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DoctrineCodeQuality\NodeAnalyzer\SetterClassMethodAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @sponsor Thanks https://www.luzanky.cz/ for sponsoring this rule
 *
 * @see related to maker bundle https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
 *
 * @see \Rector\DoctrineCodeQuality\Tests\Rector\ClassMethod\MakeEntitySetterNullabilityInSyncWithPropertyRector\MakeEntitySetterNullabilityInSyncWithPropertyRectorTest
 */
final class MakeEntitySetterNullabilityInSyncWithPropertyRector extends AbstractRector
{
    /**
     * @var SetterClassMethodAnalyzer
     */
    private $setterClassMethodAnalyzer;

    public function __construct(SetterClassMethodAnalyzer $setterClassMethodAnalyzer)
    {
        $this->setterClassMethodAnalyzer = $setterClassMethodAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Make nullability in setter class method with respect to property', [
            new CodeSample(
                <<<'PHP'
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
PHP

                ,
                <<<'PHP'
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
PHP
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        // is setter in doctrine?
        if (! $this->isInDoctrineEntityClass($node)) {
            return null;
        }

        $property = $this->setterClassMethodAnalyzer->matchNullalbeClassMethodProperty($node);
        if ($property === null) {
            return null;
        }

        /** @var PhpDocInfo|null $phpDocInfo */
        $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);
        if ($phpDocInfo === null) {
            return null;
        }

        $manyToOneTagValueNode = $phpDocInfo->getByType(ManyToOneTagValueNode::class);
        if ($manyToOneTagValueNode === null) {
            return null;
        }

        $param = $node->params[0];

        /** @var NullableType $paramType */
        $paramType = $param->type;

        $param->type = $paramType->type;

        return $node;
    }
}
