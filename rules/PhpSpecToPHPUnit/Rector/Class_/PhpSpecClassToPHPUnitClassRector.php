<?php

declare (strict_types=1);
namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
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
use Rector\PHPStanStaticTypeMapper\Enum\TypeKind;
use Rector\PHPUnit\NodeFactory\SetUpClassMethodFactory;
/**
 * @see \Rector\Tests\PhpSpecToPHPUnit\Rector\Variable\PhpSpecToPHPUnitRector\PhpSpecToPHPUnitRectorTest
 */
final class PhpSpecClassToPHPUnitClassRector extends \Rector\PhpSpecToPHPUnit\Rector\AbstractPhpSpecToPHPUnitRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassInsertManipulator
     */
    private $classInsertManipulator;
    /**
     * @readonly
     * @var \Rector\PhpSpecToPHPUnit\LetManipulator
     */
    private $letManipulator;
    /**
     * @readonly
     * @var \Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming
     */
    private $phpSpecRenaming;
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeFactory\SetUpClassMethodFactory
     */
    private $setUpClassMethodFactory;
    public function __construct(\Rector\Core\NodeManipulator\ClassInsertManipulator $classInsertManipulator, \Rector\PhpSpecToPHPUnit\LetManipulator $letManipulator, \Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming $phpSpecRenaming, \Rector\PHPUnit\NodeFactory\SetUpClassMethodFactory $setUpClassMethodFactory)
    {
        $this->classInsertManipulator = $classInsertManipulator;
        $this->letManipulator = $letManipulator;
        $this->phpSpecRenaming = $phpSpecRenaming;
        $this->setUpClassMethodFactory = $setUpClassMethodFactory;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isInPhpSpecBehavior($node)) {
            return null;
        }
        // 1. change namespace name to PHPUnit-like
        $this->phpSpecRenaming->renameNamespace($node);
        $propertyName = $this->phpSpecRenaming->resolveObjectPropertyName($node);
        $this->phpSpecRenaming->renameClass($node);
        $this->phpSpecRenaming->renameExtends($node);
        $testedClass = $this->phpSpecRenaming->resolveTestedClass($node);
        $testedObjectType = new \PHPStan\Type\ObjectType($testedClass);
        $this->classInsertManipulator->addPropertyToClass($node, $propertyName, $testedObjectType);
        $classMethod = $node->getMethod('let');
        // add let if missing
        if (!$classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            if (!$this->letManipulator->isLetNeededInClass($node)) {
                return null;
            }
            $letClassMethod = $this->createLetClassMethod($propertyName, $testedObjectType);
            $this->classInsertManipulator->addAsFirstMethod($node, $letClassMethod);
        }
        return $this->removeSelfTypeMethod($node, $testedObjectType);
    }
    private function createLetClassMethod(string $propertyName, \PHPStan\Type\ObjectType $testedObjectType) : \PhpParser\Node\Stmt\ClassMethod
    {
        $propertyFetch = new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), $propertyName);
        $testedObjectType = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($testedObjectType, \Rector\PHPStanStaticTypeMapper\Enum\TypeKind::RETURN());
        if (!$testedObjectType instanceof \PhpParser\Node\Name) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $new = new \PhpParser\Node\Expr\New_($testedObjectType);
        $assign = new \PhpParser\Node\Expr\Assign($propertyFetch, $new);
        return $this->setUpClassMethodFactory->createSetUpMethod([$assign]);
    }
    /**
     * This is already checked on construction of object
     */
    private function removeSelfTypeMethod(\PhpParser\Node\Stmt\Class_ $class, \PHPStan\Type\ObjectType $testedObjectType) : \PhpParser\Node\Stmt\Class_
    {
        foreach ($class->getMethods() as $classMethod) {
            $classMethodStmts = (array) $classMethod->stmts;
            if (\count($classMethodStmts) !== 1) {
                continue;
            }
            $innerClassMethodStmt = $this->resolveFirstNonExpressionStmt($classMethodStmts);
            if (!$innerClassMethodStmt instanceof \PhpParser\Node\Expr\MethodCall) {
                continue;
            }
            if (!$this->isName($innerClassMethodStmt->name, 'shouldHaveType')) {
                continue;
            }
            if (!isset($innerClassMethodStmt->args[0])) {
                continue;
            }
            if (!$innerClassMethodStmt->args[0] instanceof \PhpParser\Node\Arg) {
                continue;
            }
            // not the tested type
            if (!$this->valueResolver->isValue($innerClassMethodStmt->args[0]->value, $testedObjectType->getClassName())) {
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
    private function resolveFirstNonExpressionStmt(array $stmts) : ?\PhpParser\Node
    {
        if (!isset($stmts[0])) {
            return null;
        }
        $firstStmt = $stmts[0];
        if ($firstStmt instanceof \PhpParser\Node\Stmt\Expression) {
            return $firstStmt->expr;
        }
        return $firstStmt;
    }
}
