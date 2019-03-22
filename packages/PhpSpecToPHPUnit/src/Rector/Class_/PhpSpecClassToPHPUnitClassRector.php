<?php declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
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
        PHPUnitTypeDeclarationDecorator $phpUnitTypeDeclarationDecorator,
        string $objectBehaviorClass = 'PhpSpec\ObjectBehavior'
    ) {
        $this->classManipulator = $classManipulator;
        $this->phpSpecRenaming = $phpSpecRenaming;
        $this->phpUnitTypeDeclarationDecorator = $phpUnitTypeDeclarationDecorator;

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

        // add let if missing
        if ($node->getMethod('let') === null) {
            $letClassMethod = $this->createLetClassMethod($propertyName);
            $this->classManipulator->addAsFirstMethod($node, $letClassMethod);
        }

        return $node;
    }

    private function createLetClassMethod(string $propertyName): ClassMethod
    {
        $propertyFetch = new PropertyFetch(new Variable('this'), $propertyName);
        $newClass = new New_(new FullyQualified($this->testedClass));

        $letClassMethod = new ClassMethod(new Identifier('let'));
        $letClassMethod->stmts[] = new Expression(new Assign($propertyFetch, $newClass));

        $this->phpUnitTypeDeclarationDecorator->decorate($letClassMethod);

        return $letClassMethod;
    }
}
