<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Scalar\LNumber;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Property;
use PhpParser\Node\Stmt\PropertyProperty;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\DoctrineCodeQuality\NodeAnalyzer\DoctrinePropertyAnalyzer;

/**
 * @sponsor Thanks https://www.luzanky.cz/ for sponsoring this rule
 *
 * @see \Rector\DoctrineCodeQuality\Tests\Rector\Property\CorrectDefaultTypesOnEntityPropertyRector\CorrectDefaultTypesOnEntityPropertyRectorTest
 */
final class CorrectDefaultTypesOnEntityPropertyRector extends AbstractRector
{
    /**
     * @var DoctrinePropertyAnalyzer
     */
    private $doctrinePropertyAnalyzer;

    public function __construct(DoctrinePropertyAnalyzer $doctrinePropertyAnalyzer)
    {
        $this->doctrinePropertyAnalyzer = $doctrinePropertyAnalyzer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change default value types to match Doctrine annotation type', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @ORM\Column(name="is_old", type="boolean")
     */
    private $isOld = '0';
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @ORM\Column(name="is_old", type="boolean")
     */
    private $isOld = false;
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Property::class];
    }

    /**
     * @param Property $node
     */
    public function refactor(Node $node): ?Node
    {
        $columnTagValueNode = $this->doctrinePropertyAnalyzer->matchDoctrineColumnTagValueNode($node);
        if ($columnTagValueNode === null) {
            return null;
        }

        $onlyProperty = $node->props[0];

        $defaultValue = $onlyProperty->default;
        if ($defaultValue === null) {
            return null;
        }

        if (in_array($columnTagValueNode->getType(), ['bool', 'boolean'], true)) {
            return $this->refactorToBoolType($onlyProperty, $node);
        }

        if (in_array($columnTagValueNode->getType(), ['int', 'integer', 'bigint', 'smallint'], true)) {
            return $this->refactorToIntType($onlyProperty, $node);
        }

        return null;
    }

    private function refactorToBoolType(PropertyProperty $propertyProperty, Property $property): ?Property
    {
        if ($propertyProperty->default === null) {
            return null;
        }

        $defaultExpr = $propertyProperty->default;
        if ($defaultExpr instanceof String_) {
            $propertyProperty->default = boolval($defaultExpr->value) ? $this->createTrue() : $this->createFalse();
            return $property;
        }
        if ($defaultExpr instanceof ConstFetch) {
            // already ok
            return null;
        }

        throw new NotImplementedYetException();
    }

    private function refactorToIntType(PropertyProperty $propertyProperty, Property $property): ?Property
    {
        if ($propertyProperty->default === null) {
            return null;
        }

        $defaultExpr = $propertyProperty->default;
        if ($defaultExpr instanceof String_) {
            $propertyProperty->default = new LNumber((int) $defaultExpr->value);
            return $property;
        }

        if ($defaultExpr instanceof LNumber) {
            // already correct
            return null;
        }

        throw new NotImplementedYetException();
    }
}
