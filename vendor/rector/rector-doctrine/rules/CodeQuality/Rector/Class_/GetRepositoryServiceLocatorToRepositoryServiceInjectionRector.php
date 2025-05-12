<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Function_;
use PhpParser\NodeVisitor;
use PHPStan\Reflection\ClassReflection;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\Doctrine\Enum\TestClass;
use Rector\Doctrine\NodeAnalyzer\RepositoryClassResolver;
use Rector\Naming\Naming\PropertyNaming;
use Rector\NodeManipulator\ClassDependencyManipulator;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPStan\ScopeFetcher;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Rector\Rector\AbstractRector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\CodeQuality\Rector\Class_\GetRepositoryServiceLocatorToRepositoryServiceInjectionRector\GetRepositoryServiceLocatorToRepositoryServiceInjectionRectorTest
 */
final class GetRepositoryServiceLocatorToRepositoryServiceInjectionRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ValueResolver $valueResolver;
    /**
     * @readonly
     */
    private PropertyNaming $propertyNaming;
    /**
     * @readonly
     */
    private RepositoryClassResolver $repositoryClassResolver;
    /**
     * @readonly
     */
    private ClassDependencyManipulator $classDependencyManipulator;
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @var string
     */
    private const GET_REPOSITORY_METHOD = 'getRepository';
    public function __construct(ValueResolver $valueResolver, PropertyNaming $propertyNaming, RepositoryClassResolver $repositoryClassResolver, ClassDependencyManipulator $classDependencyManipulator, ReflectionProvider $reflectionProvider)
    {
        $this->valueResolver = $valueResolver;
        $this->propertyNaming = $propertyNaming;
        $this->repositoryClassResolver = $repositoryClassResolver;
        $this->classDependencyManipulator = $classDependencyManipulator;
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns $this->entityManager->getRepository(...) on entity that suppors service repository, to constructor injection', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="SomeRepository")
 */
class SomeEntity
{
}

use Doctrine\Bundle\MongoDBBundle\Repository\ServiceDocumentRepository;

final class SomeRepository extends ServiceDocumentRepository
{
}

final class SomeClass
{
    public function run()
    {
        return $this->getRepository(SomeEntity::class)->find(1);
    }
}

CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeClass
{
    public function __construct(
        private ServiceDocumentRepository $someEntityRepository
    ) {
    }

    public function run()
    {
        return $this->someEntityRepository->find(1);
    }
}
CODE_SAMPLE
)]);
    }
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Class_
    {
        if ($this->shouldSkipClass($node)) {
            return null;
        }
        $repositoryPropertyMetadatas = [];
        $this->traverseNodesWithCallable($node->stmts, function (Node $node) use(&$repositoryPropertyMetadatas) {
            if ($node instanceof Class_ || $node instanceof Function_) {
                // avoid nested anonymous class or function
                return NodeVisitor::DONT_TRAVERSE_CURRENT_AND_CHILDREN;
            }
            if (!$node instanceof MethodCall) {
                return null;
            }
            if (!$this->isName($node->name, self::GET_REPOSITORY_METHOD)) {
                return null;
            }
            if ($node->isFirstClassCallable()) {
                return null;
            }
            if (\count($node->getArgs()) !== 1) {
                return null;
            }
            $fetchedEntityValue = $node->getArgs()[0]->value;
            // must be constant value, not dynamic method call or variable
            $entityClassName = $this->valueResolver->getValue($fetchedEntityValue);
            if ($entityClassName === null) {
                return null;
            }
            $repositoryVariableName = $this->propertyNaming->fqnToVariableName($entityClassName) . 'Repository';
            $repositoryClass = $this->repositoryClassResolver->resolveFromEntityClass($entityClassName);
            // unable to resolve
            if (!\is_string($repositoryClass)) {
                return null;
            }
            $repositoryClassReflection = $this->reflectionProvider->getClass($repositoryClass);
            if (!$repositoryClassReflection->is(DoctrineClass::SERVICE_DOCUMENT_REPOSITORY) && !$repositoryClassReflection->is(DoctrineClass::SERVICE_ENTITY_REPOSITORY)) {
                return null;
            }
            $repositoryPropertyMetadatas[] = new PropertyMetadata($repositoryVariableName, new FullyQualifiedObjectType($repositoryClass));
            return new PropertyFetch(new Variable('this'), new Identifier($repositoryVariableName));
        });
        if ($repositoryPropertyMetadatas === []) {
            return null;
        }
        foreach ($repositoryPropertyMetadatas as $repositoryPropertyMetadata) {
            $this->classDependencyManipulator->addConstructorDependency($node, $repositoryPropertyMetadata);
        }
        return $node;
    }
    private function shouldSkipClass(Class_ $class) : bool
    {
        // keep it safe
        if (!$class->isFinal() || $class->isAnonymous()) {
            return \true;
        }
        $classScope = ScopeFetcher::fetch($class);
        $classReflection = $classScope->getClassReflection();
        if (!$classReflection instanceof ClassReflection) {
            return \true;
        }
        // skip repositories themselves to avoid circular dependencies
        if ($classReflection->is(DoctrineClass::OBJECT_REPOSITORY)) {
            return \true;
        }
        if ($classReflection->is(DoctrineClass::ENTITY_REPOSITORY)) {
            return \true;
        }
        return $classReflection->is(TestClass::BEHAT_CONTEXT);
    }
}
