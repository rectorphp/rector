<?php

declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\PhpParser\Node\Manipulator\ClassInsertManipulator;
use Rector\PhpSpecToPHPUnit\LetManipulator;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\Rector\AbstractPhpSpecToPHPUnitRector;
use Rector\PHPUnit\NodeFactory\SetUpClassMethodFactory;

/**
 * @see \Rector\PhpSpecToPHPUnit\Tests\Rector\Variable\PhpSpecToPHPUnitRector\PhpSpecToPHPUnitRectorTest
 */
final class PhpSpecClassToPHPUnitClassRector extends AbstractPhpSpecToPHPUnitRector
{
    /**
     * @var ObjectType
     */
    private $testedObjectType;

    /**
     * @var PhpSpecRenaming
     */
    private $phpSpecRenaming;

    /**
     * @var LetManipulator
     */
    private $letManipulator;

    /**
     * @var ClassInsertManipulator
     */
    private $classInsertManipulator;

    /**
     * @var SetUpClassMethodFactory
     */
    private $setUpClassMethodFactory;

    public function __construct(
        ClassInsertManipulator $classInsertManipulator,
        LetManipulator $letManipulator,
        PhpSpecRenaming $phpSpecRenaming,
        SetUpClassMethodFactory $setUpClassMethodFactory
    ) {
        $this->phpSpecRenaming = $phpSpecRenaming;
        $this->letManipulator = $letManipulator;
        $this->classInsertManipulator = $classInsertManipulator;
        $this->setUpClassMethodFactory = $setUpClassMethodFactory;
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

        $testedClass = $this->phpSpecRenaming->resolveTestedClass($node);

        $this->testedObjectType = new ObjectType($testedClass);
        $this->classInsertManipulator->addPropertyToClass($node, $propertyName, $this->testedObjectType);
        $classMethod = $node->getMethod('let');

        // add let if missing
        if ($classMethod === null) {
            if (! $this->letManipulator->isLetNeededInClass($node)) {
                return null;
            }

            $letClassMethod = $this->createLetClassMethod($propertyName);
            $this->classInsertManipulator->addAsFirstMethod($node, $letClassMethod);
        }

        return $this->removeSelfTypeMethod($node);
    }

    private function createLetClassMethod(string $propertyName): ClassMethod
    {
        $propertyFetch = new PropertyFetch(new Variable('this'), $propertyName);

        $testedObjectType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($this->testedObjectType);
        if (! $testedObjectType instanceof Name) {
            throw new ShouldNotHappenException();
        }

        $new = new New_($testedObjectType);

        $assign = new Assign($propertyFetch, $new);

        return $this->setUpClassMethodFactory->createSetUpMethod([$assign]);
    }

    /**
     * This is already checked on construction of object
     */
    private function removeSelfTypeMethod(Class_ $class): Class_
    {
        foreach ($class->getMethods() as $classMethod) {
            if (count((array) $classMethod->stmts) !== 1) {
                continue;
            }

            $innerClassMethodStmt = $classMethod->stmts[0] instanceof Expression ? $classMethod->stmts[0]->expr : $classMethod->stmts[0];

            if (! $innerClassMethodStmt instanceof MethodCall) {
                continue;
            }

            if (! $this->isName($innerClassMethodStmt->name, 'shouldHaveType')) {
                continue;
            }

            // not the tested type
            if (! $this->isValue($innerClassMethodStmt->args[0]->value, $this->testedObjectType->getClassName())) {
                continue;
            }

            // remove it
            $this->removeNodeFromStatements($class, $classMethod);
        }

        return $class;
    }
}
