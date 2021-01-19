<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Core\Rector\AbstractRector;
use Rector\DoctrineCodeQuality\NodeAnalyzer\SetterClassMethodAnalyzer;
use Rector\DoctrineCodeQuality\NodeManipulator\PropertyTypeManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @sponsor Thanks https://www.luzanky.cz/ for sponsoring this rule
 *
 * @see related to maker bundle https://symfony.com/doc/current/bundles/SymfonyMakerBundle/index.html
 *
 * @see \Rector\DoctrineCodeQuality\Tests\Rector\ClassMethod\MakeEntityDateTimePropertyDateTimeInterfaceRector\MakeEntityDateTimePropertyDateTimeInterfaceRectorTest
 */
final class MakeEntityDateTimePropertyDateTimeInterfaceRector extends AbstractRector
{
    /**
     * @var SetterClassMethodAnalyzer
     */
    private $setterClassMethodAnalyzer;

    /**
     * @var PropertyTypeManipulator
     */
    private $propertyTypeManipulator;

    public function __construct(
        SetterClassMethodAnalyzer $setterClassMethodAnalyzer,
        PropertyTypeManipulator $propertyTypeManipulator
    ) {
        $this->setterClassMethodAnalyzer = $setterClassMethodAnalyzer;
        $this->propertyTypeManipulator = $propertyTypeManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Make maker bundle generate DateTime property accept DateTimeInterface too',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class User
{
    /**
     * @var DateTime|null
     */
    private $bornAt;

    public function setBornAt(DateTimeInterface $bornAt)
    {
        $this->bornAt = $bornAt;
    }
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
     * @var DateTimeInterface|null
     */
    private $bornAt;

    public function setBornAt(DateTimeInterface $bornAt)
    {
        $this->bornAt = $bornAt;
    }
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
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        $property = $this->setterClassMethodAnalyzer->matchDateTimeSetterProperty($node);
        if (! $property instanceof Property) {
            return null;
        }

        if (! $this->isObjectType($property, 'DateTime')) {
            return null;
        }

        $this->propertyTypeManipulator->changePropertyType($property, 'DateTime', 'DateTimeInterface');

        return $node;
    }
}
