<?php declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\PhpParser\Node\VariableInfo;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\PHPUnitTypeDeclarationDecorator;
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

    /**
     * @var PHPUnitTypeDeclarationDecorator
     */
    private $phpUnitTypeDeclarationDecorator;

    public function __construct(
        ClassManipulator $classManipulator,
        PhpSpecRenaming $phpSpecRenaming,
        PHPUnitTypeDeclarationDecorator $phpUnitTypeDeclarationDecorator
    ) {
        $this->classManipulator = $classManipulator;
        $this->phpSpecRenaming = $phpSpecRenaming;
        $this->phpUnitTypeDeclarationDecorator = $phpUnitTypeDeclarationDecorator;
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

        // add let if missing
        if ($node->getMethod('let') === null) {
            $letClassMethod = $this->createLetClassMethod($propertyName);
            $this->classManipulator->addAsFirstMethod($node, $letClassMethod);
        }

        return $this->removeSelfTypeMethod($node);
    }

    private function createLetClassMethod(string $propertyName): ClassMethod
    {
        $propertyFetch = new PropertyFetch(new Variable('this'), $propertyName);
        $newClass = new New_(new FullyQualified($this->testedClass));

        $letClassMethod = new ClassMethod(new Identifier('setUp'));
        $this->makeProtected($letClassMethod);
        $letClassMethod->stmts[] = new Expression(new Assign($propertyFetch, $newClass));

        $this->phpUnitTypeDeclarationDecorator->decorate($letClassMethod);

        return $letClassMethod;
    }

    /**
     * This is already checked on construction of object
     */
    private function removeSelfTypeMethod(Class_ $node): Class_
    {
        foreach ((array) $node->stmts as $key => $stmt) {
            if (! $stmt instanceof ClassMethod) {
                continue;
            }

            if (count((array) $stmt->stmts) !== 1) {
                continue;
            }

            $innerClassMethodStmt = $stmt->stmts[0] instanceof Expression ? $stmt->stmts[0]->expr : $stmt->stmts[0];

            if (! $innerClassMethodStmt instanceof MethodCall) {
                continue;
            }

            if (! $this->isName($innerClassMethodStmt, 'shouldHaveType')) {
                continue;
            }

            // not the tested type
            if (! $this->isValue($innerClassMethodStmt->args[0]->value, $this->testedClass)) {
                continue;
            }

            // remove it
            unset($node->stmts[$key]);
        }

        return $node;
    }
}
