<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\MethodCall;

use RectorPrefix202306\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\NodeManipulator\ClassDependencyManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\Set\DoctrineRepositoryAsServiceSet\DoctrineRepositoryAsServiceSetTest
 */
final class ReplaceParentRepositoryCallsByRepositoryPropertyRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassDependencyManipulator
     */
    private $classDependencyManipulator;
    /**
     * @var string[]
     */
    private const ENTITY_REPOSITORY_PUBLIC_METHODS = ['createQueryBuilder', 'createResultSetMappingBuilder', 'clear', 'find', 'findBy', 'findAll', 'findOneBy', 'count', 'getClassName', 'matching'];
    public function __construct(ClassDependencyManipulator $classDependencyManipulator)
    {
        $this->classDependencyManipulator = $classDependencyManipulator;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Handles method calls in child of Doctrine EntityRepository and moves them to $this->repository property.', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\EntityRepository;

class SomeRepository extends EntityRepository
{
    public function someMethod()
    {
        return $this->findAll();
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\EntityRepository;

class SomeRepository extends EntityRepository
{
    public function someMethod()
    {
        return $this->repository->findAll();
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        $class = $node;
        $this->traverseNodesWithCallable($node->getMethods(), function (Node $node) use(&$hasChanged, $class) {
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isObjectType($node->var, new ObjectType('Doctrine\\ORM\\EntityRepository'))) {
                return null;
            }
            if (!$this->isNames($node->name, self::ENTITY_REPOSITORY_PUBLIC_METHODS)) {
                return null;
            }
            if ($node->isFirstClassCallable()) {
                return null;
            }
            $hasChanged = \true;
            // is it getRepository(), replace it with DI property
            if ($node->var instanceof MethodCall && $this->isName($node->var->name, 'getRepository')) {
                return $this->refactorGetRepositoryMethodCall($class, $node);
            }
            $node->var = $this->nodeFactory->createPropertyFetch('this', 'repository');
        });
        if ($hasChanged) {
            // @todo add constructor property here
            return $node;
        }
        return null;
    }
    private function resolveRepositoryName(Expr $expr) : string
    {
        $entityReferenceName = $this->valueResolver->getValue($expr);
        if (!\is_string($entityReferenceName)) {
            throw new ShouldNotHappenException();
        }
        $lastNamePart = (string) Strings::after($entityReferenceName, '\\', -1);
        return \lcfirst($lastNamePart) . 'Repository';
    }
    private function guessRepositoryType(Expr $expr) : ObjectType
    {
        if ($expr instanceof ClassConstFetch) {
            $entityClass = $this->getName($expr->class);
            if ($entityClass === null) {
                return new ObjectType('Unknown_Repository_Class');
            }
            $entityClassNamespace = (string) Strings::before($entityClass, '\\', -2);
            $lastNamePart = (string) Strings::after($entityClass, '\\', -1);
            return new ObjectType($entityClassNamespace . '\\Repository\\' . $lastNamePart . 'Repository');
        }
        return new ObjectType('Unknown_Repository_Class');
    }
    private function refactorGetRepositoryMethodCall(Class_ $class, MethodCall $methodCall) : ?MethodCall
    {
        /** @var MethodCall $parentMethodCall */
        $parentMethodCall = $methodCall->var;
        if (\count($parentMethodCall->args) !== 1) {
            return null;
        }
        if ($this->isObjectType($class, new ObjectType('Doctrine\\ORM\\EntityRepository'))) {
            return null;
        }
        $firstArgValue = $parentMethodCall->getArgs()[0]->value;
        $repositoryPropertyName = $this->resolveRepositoryName($firstArgValue);
        $repositoryType = $this->guessRepositoryType($firstArgValue);
        $propertyMetadata = new PropertyMetadata($repositoryPropertyName, $repositoryType);
        $this->classDependencyManipulator->addConstructorDependency($class, $propertyMetadata);
        $methodCall->var = $this->nodeFactory->createPropertyFetch('this', $repositoryPropertyName);
        return $methodCall;
    }
}
