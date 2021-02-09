<?php

declare(strict_types=1);

namespace Rector\DoctrineGedmoToKnplabs\NodeFactory;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObjectFactory\PhpDocNode\Doctrine\EntityTagValueNodeFactory;
use Rector\Core\NodeManipulator\ClassInsertManipulator;

final class TranslationClassNodeFactory
{
    /**
     * @var PhpDocInfoFactory
     */
    private $phpDocInfoFactory;

    /**
     * @var ClassInsertManipulator
     */
    private $classInsertManipulator;

    /**
     * @var EntityTagValueNodeFactory
     */
    private $entityTagValueNodeFactory;

    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ClassInsertManipulator $classInsertManipulator,
    EntityTagValueNodeFactory $entityTagValueNodeFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->classInsertManipulator = $classInsertManipulator;
        $this->entityTagValueNodeFactory = $entityTagValueNodeFactory;
    }

    public function create(string $classShortName): Class_
    {
        $class = new Class_($classShortName);
        $class->implements[] = new FullyQualified('Knp\DoctrineBehaviors\Contract\Entity\TranslationInterface');
        $this->classInsertManipulator->addAsFirstTrait(
            $class,
            'Knp\DoctrineBehaviors\Model\Translatable\TranslationTrait'
        );

        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($class);

        $entityTagValueNode = $this->entityTagValueNodeFactory->create();
        $phpDocInfo->addTagValueNodeWithShortName($entityTagValueNode);

        return $class;
    }
}
