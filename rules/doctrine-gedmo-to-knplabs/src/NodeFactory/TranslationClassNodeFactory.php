<?php

declare(strict_types=1);

namespace Rector\DoctrineGedmoToKnplabs\NodeFactory;

use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\EntityTagValueNode;
use Rector\Core\PhpParser\Node\Manipulator\ClassInsertManipulator;

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

    public function __construct(PhpDocInfoFactory $phpDocInfoFactory, ClassInsertManipulator $classInsertManipulator)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
        $this->classInsertManipulator = $classInsertManipulator;
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
        $phpDocInfo->addTagValueNodeWithShortName(new EntityTagValueNode([]));

        return $class;
    }
}
