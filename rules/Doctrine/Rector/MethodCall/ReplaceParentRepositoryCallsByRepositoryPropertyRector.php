<?php

declare(strict_types=1);

namespace Rector\Doctrine\Rector\MethodCall;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeManipulator\ClassDependencyManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PostRector\ValueObject\PropertyMetadata;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\DoctrineRepositoryAsServiceSetTest
 */
final class ReplaceParentRepositoryCallsByRepositoryPropertyRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private const ENTITY_REPOSITORY_PUBLIC_METHODS = [
        'createQueryBuilder',
        'createResultSetMappingBuilder',
        'clear',
        'find',
        'findBy',
        'findAll',
        'findOneBy',
        'count',
        'getClassName',
        'matching',
    ];
    /**
     * @var ClassDependencyManipulator
     */
    private $classDependencyManipulator;

    public function __construct(ClassDependencyManipulator $classDependencyManipulator)
    {
        $this->classDependencyManipulator = $classDependencyManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Handles method calls in child of Doctrine EntityRepository and moves them to $this->repository property.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
use Doctrine\ORM\EntityRepository;

class SomeRepository extends EntityRepository
{
    public function someMethod()
    {
        return $this->findAll();
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
use Doctrine\ORM\EntityRepository;

class SomeRepository extends EntityRepository
{
    public function someMethod()
    {
        return $this->repository->findAll();
    }
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectType($node->var, new ObjectType('Doctrine\ORM\EntityRepository'))) {
            return null;
        }

        if (! $this->isNames($node->name, self::ENTITY_REPOSITORY_PUBLIC_METHODS)) {
            return null;
        }

        // is it getRepository(), replace it with DI property
        if ($node->var instanceof MethodCall) {
            if ($this->isName($node->var->name, 'getRepository')) {
                if (count($node->var->args) === 1) {
                    $firstArgValue = $node->var->args[0]->value;

                    $repositoryPropertyName = $this->resolveRepositoryName($firstArgValue);
                    $repositoryType = $this->guessRepositoryType($firstArgValue);

                    $class = $node->getAttribute(AttributeKey::CLASS_NODE);

                    $propertyMetadata = new PropertyMetadata($repositoryPropertyName, new ObjectType($repositoryType), Class_::MODIFIER_PRIVATE);
                    $this->classDependencyManipulator->addConstructorDependency($class, $propertyMetadata);

                    $node->var = $this->nodeFactory->createPropertyFetch('this', $repositoryPropertyName);
                    return $node;
                }
            }
        }

        $node->var = $this->nodeFactory->createPropertyFetch('this', 'repository');

        return $node;
    }

    private function resolveRepositoryName(Expr $expr): string
    {
        $entityReferenceName = $this->valueResolver->getValue($expr);
        $lastNamePart = (string) Strings::after($entityReferenceName, '\\', -1);
        return lcfirst($lastNamePart) . 'Repository';
    }

    private function guessRepositoryType(Expr $expr): string
    {
        if ($expr instanceof ClassConstFetch) {
            $entityClass = $this->getName($expr->class);
            if ($entityClass === null) {
                return 'Unknown_Repository_Class';;
            }

            $entityClassNamespace = (string) Strings::before($entityClass, '\\', -2);

            $lastNamePart = (string) Strings::after($entityClass, '\\', -1);
            return $entityClassNamespace . '\\Repository\\' . $entityClass . '\\' . $lastNamePart;
        }

        return 'Unknown_Repository_Class';
    }
}
