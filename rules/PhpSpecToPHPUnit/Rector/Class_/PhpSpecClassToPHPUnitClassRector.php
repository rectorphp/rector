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
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\PhpSpecToPHPUnit\LetManipulator;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\Rector\AbstractPhpSpecToPHPUnitRector;
use Rector\PHPStanStaticTypeMapper\ValueObject\TypeKind;
use Rector\PHPUnit\NodeFactory\SetUpClassMethodFactory;

/**
 * @see \Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\PhpSpecToPHPUnitRectorTest
 */
final class PhpSpecClassToPHPUnitClassRector extends AbstractPhpSpecToPHPUnitRector
{
    /**
     * @var string
     */
    private const ALREADY_RENAMED_TO_TEST = 'already_renamed_to_test';

    public function __construct(
        private ClassInsertManipulator $classInsertManipulator,
        private LetManipulator $letManipulator,
        private PhpSpecRenaming $phpSpecRenaming,
        private SetUpClassMethodFactory $setUpClassMethodFactory
    ) {
    }

    /**
     * @return array<class-string<Node>>
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

        $isAlreadyRenamedToTest = $node->getAttribute(self::ALREADY_RENAMED_TO_TEST);
        if ($isAlreadyRenamedToTest) {
            return null;
        }

        // 1. change namespace name to PHPUnit-like
        $this->phpSpecRenaming->renameNamespace($node);

        $propertyName = $this->phpSpecRenaming->resolveObjectPropertyName($node);

        $node->setAttribute(self::ALREADY_RENAMED_TO_TEST, true);
        $this->phpSpecRenaming->renameClass($node);
        $this->phpSpecRenaming->renameExtends($node);

        $testedClass = $this->phpSpecRenaming->resolveTestedClass($node);

        $testedObjectType = new ObjectType($testedClass);
        $this->classInsertManipulator->addPropertyToClass($node, $propertyName, $testedObjectType);
        $classMethod = $node->getMethod('let');

        // add let if missing
        if (! $classMethod instanceof ClassMethod) {
            if (! $this->letManipulator->isLetNeededInClass($node)) {
                return null;
            }

            $letClassMethod = $this->createLetClassMethod($propertyName, $testedObjectType);
            $this->classInsertManipulator->addAsFirstMethod($node, $letClassMethod);
        }

        return $this->removeSelfTypeMethod($node, $testedObjectType);
    }

    private function createLetClassMethod(string $propertyName, ObjectType $testedObjectType): ClassMethod
    {
        $propertyFetch = new PropertyFetch(new Variable('this'), $propertyName);

        $testedObjectType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode(
            $testedObjectType,
            TypeKind::RETURN()
        );
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
    private function removeSelfTypeMethod(Class_ $class, ObjectType $testedObjectType): Class_
    {
        foreach ($class->getMethods() as $classMethod) {
            $classMethodStmts = (array) $classMethod->stmts;
            if (count($classMethodStmts) !== 1) {
                continue;
            }

            $innerClassMethodStmt = $this->resolveFirstNonExpressionStmt($classMethodStmts);
            if (! $innerClassMethodStmt instanceof MethodCall) {
                continue;
            }

            if (! $this->isName($innerClassMethodStmt->name, 'shouldHaveType')) {
                continue;
            }

            // not the tested type
            if (! $this->valueResolver->isValue(
                $innerClassMethodStmt->args[0]->value,
                $testedObjectType->getClassName()
            )) {
                continue;
            }

            // remove it
            $this->removeNodeFromStatements($class, $classMethod);
        }

        return $class;
    }

    /**
     * @param Stmt[] $stmts
     */
    private function resolveFirstNonExpressionStmt(array $stmts): ?Node
    {
        if (! isset($stmts[0])) {
            return null;
        }

        $firstStmt = $stmts[0];
        if ($firstStmt instanceof Expression) {
            return $firstStmt->expr;
        }

        return $firstStmt;
    }
}
