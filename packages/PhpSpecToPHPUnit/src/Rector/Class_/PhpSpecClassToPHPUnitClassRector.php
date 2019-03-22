<?php declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\PhpParser\Node\VariableInfo;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\Rector\AbstractPhpSpecToPHPUnitRector;

final class PhpSpecClassToPHPUnitClassRector extends AbstractPhpSpecToPHPUnitRector
{
    /**
     * @var ClassManipulator
     */
    private $classManipulator;

    /**
     * @var string
     */
    private $testedClass;

    /**
     * @var PhpSpecRenaming
     */
    private $phpSpecRenaming;

    public function __construct(
        ClassManipulator $classManipulator,
        PhpSpecRenaming $phpSpecRenaming,
        string $objectBehaviorClass = 'PhpSpec\ObjectBehavior'
    ) {
        $this->classManipulator = $classManipulator;
        $this->phpSpecRenaming = $phpSpecRenaming;

        parent::__construct($objectBehaviorClass);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInPhpSpecBehavior($node)) {
            return null;
        }

        // 1. change namespace name to PHPUnit-like
        $this->phpSpecRenaming->renameNamespace($node);

        $propertyName = $this->phpSpecRenaming->resolveObjectPropertyName($node);

        $this->phpSpecRenaming->renameClass($node);
        $this->phpSpecRenaming->renameExtends($node);

        $this->testedClass = $this->phpSpecRenaming->resolveTestedClass($node);
        $this->classManipulator->addPropertyToClass($node, new VariableInfo($propertyName, $this->testedClass));

        return $node;
    }
}
