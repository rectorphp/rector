<?php declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\StaticTypeMapper;
use Rector\PhpParser\Node\Manipulator\ClassManipulator;
use Rector\PhpParser\Node\VariableInfo;
use Rector\PhpSpecToPHPUnit\LetManipulator;
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
     * @var ObjectType
     */
    private $testedObjectType;

    /**
     * @var PhpSpecRenaming
     */
    private $phpSpecRenaming;

    /**
     * @var PHPUnitTypeDeclarationDecorator
     */
    private $phpUnitTypeDeclarationDecorator;

    /**
     * @var LetManipulator
     */
    private $letManipulator;

    /**
     * @var StaticTypeMapper
     */
    private $staticTypeMapper;

    public function __construct(
        ClassManipulator $classManipulator,
        PhpSpecRenaming $phpSpecRenaming,
        PHPUnitTypeDeclarationDecorator $phpUnitTypeDeclarationDecorator,
        LetManipulator $letManipulator,
        StaticTypeMapper $staticTypeMapper
    ) {
        $this->classManipulator = $classManipulator;
        $this->phpSpecRenaming = $phpSpecRenaming;
        $this->phpUnitTypeDeclarationDecorator = $phpUnitTypeDeclarationDecorator;
        $this->letManipulator = $letManipulator;
        $this->staticTypeMapper = $staticTypeMapper;
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

        $this->classManipulator->addPropertyToClass($node, new VariableInfo($propertyName, $this->testedObjectType));

        // add let if missing
        if ($node->getMethod('let') === null) {
            if (! $this->letManipulator->isLetNeededInClass($node)) {
                return null;
            }

            $letClassMethod = $this->createLetClassMethod($propertyName);
            $this->classManipulator->addAsFirstMethod($node, $letClassMethod);
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

        $newClass = new New_($testedObjectType);

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
        foreach ($node->getMethods() as $classMethod) {
            if (count((array) $classMethod->stmts) !== 1) {
                continue;
            }

            $innerClassMethodStmt = $classMethod->stmts[0] instanceof Expression ? $classMethod->stmts[0]->expr : $classMethod->stmts[0];

            if (! $innerClassMethodStmt instanceof MethodCall) {
                continue;
            }

            if (! $this->isName($innerClassMethodStmt, 'shouldHaveType')) {
                continue;
            }

            // not the tested type
            if (! $this->isValue($innerClassMethodStmt->args[0]->value, $this->testedObjectType->getClassName())) {
                continue;
            }

            // remove it
            $this->removeNodeFromStatements($node, $classMethod);
        }

        return $node;
    }
}
