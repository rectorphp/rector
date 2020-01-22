<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use Rector\CakePHPToSymfony\Rector\AbstractCakePHPRector;
use Rector\CakePHPToSymfony\Rector\NodeFactory\DoctrineNodeFactory;
use Rector\CakePHPToSymfony\Rector\NodeFactory\DoctrineRepositoryClassMethodFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see https://book.cakephp.org/2/en/models/retrieving-your-data.html#find
 * @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.7/tutorials/getting-started.html#entity-repositories
 *
 * @see \Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPModelToDoctrineRepositoryRector\CakePHPModelToDoctrineRepositoryRectorTest
 */
final class CakePHPModelToDoctrineRepositoryRector extends AbstractCakePHPRector
{
    /**
     * @var DoctrineRepositoryClassMethodFactory
     */
    private $doctrineRepositoryClassMethodFactory;

    /**
     * @var DoctrineNodeFactory
     */
    private $doctrineNodeFactory;

    public function __construct(
        DoctrineRepositoryClassMethodFactory $doctrineRepositoryClassMethodFactory,
        DoctrineNodeFactory $doctrineNodeFactory
    ) {
        $this->doctrineRepositoryClassMethodFactory = $doctrineRepositoryClassMethodFactory;
        $this->doctrineNodeFactory = $doctrineNodeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Migrate CakePHP Model active record to Doctrine\ORM\Repository with repository/DQL method calls',
            [
                new CodeSample(
                    <<<'PHP'
class Activity extends \AppModel
{
    public function getAll()
    {
        $result = $this->find('all');

        return $result;
    }

    public function getOne()
    {
        $result = $this->find('first', [
            'conditions' => [
                'DocumentVersionsSave.revision_number' => $versionId,
                'DocumentVersionsSave.document_id' => $documentId,
            ],
            'order' => [
                'created DESC',
            ],
        ]);

        return $result;
    }
}
PHP
,
                    <<<'PHP'
use Doctrine\ORM\EntityManagerInterface;

class Activity
{
}

class ActivityRepository
{
    /**
     * @var EntityManagerInterface
     */
    private $repository;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Activity::class);
    }

    public function getAll()
    {
        $result = $this->repository->findAll();

        return $result;
    }

    public function getOne()
    {
        $result = $this->findOneBy([
            'revision_number' => $versionId,
            'document_id' => $documentId,
        ], 'created DESC');

        return $result;
    }
}
PHP

                ),
            ]
        );
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
        if (! $this->isInCakePHPController($node)) {
            return null;
        }

        $repositoryMethods = $this->getRepositoryMethods($node);
        if ($repositoryMethods === []) {
            return null;
        }

        // 1. create repository class
        $repositoryClass = $this->createRepositoryClass($node, $repositoryMethods);

        // 2. save repository class
        $nodeToPrint = $this->createNodeToPrint($node, $repositoryClass);

        $repositoryFilePath = $this->createRepositoryFilePath($node);
        $this->printToFile($nodeToPrint, $repositoryFilePath);

        // 3.remove repository class methods
        foreach ($repositoryMethods as $repositoryMethod) {
            $this->removeNode($repositoryMethod);
        }

        $node->extends = null;

        return $node;
    }

    /**
     * Looks for "$this->find()" call
     */
    private function inRepositoryMethod(ClassMethod $classMethod): bool
    {
        $isRepositoryMethod = false;

        $this->traverseNodesWithCallable((array) $classMethod->getStmts(), function (Node $node) use (
            &$isRepositoryMethod
        ) {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if (! $this->isObjectType($node->var, 'AppModel')) {
                return null;
            }

            if (! $this->isName($node->name, 'find')) {
                return null;
            }

            $isRepositoryMethod = true;

            return NodeTraverser::STOP_TRAVERSAL;
        });

        return $isRepositoryMethod;
    }

    /**
     * @return ClassMethod[]
     */
    private function getRepositoryMethods(Class_ $class): array
    {
        $repositoryMethods = [];

        foreach ($class->getMethods() as $classMethod) {
            if (! $this->inRepositoryMethod($classMethod)) {
                continue;
            }

            $repositoryMethods[] = $classMethod;
        }

        return $repositoryMethods;
    }

    private function createNodeToPrint(Class_ $class, Class_ $repositoryClass): Node
    {
        /** @var Namespace_|null $namespaceNode */
        $namespaceNode = $class->getAttribute(AttributeKey::NAMESPACE_NODE);
        if ($namespaceNode !== null) {
            $namespaceNode->stmts = [$repositoryClass];
            return $namespaceNode;
        }

        return $repositoryClass;
    }

    private function createRepositoryFilePath(Class_ $class): string
    {
        $repositoryClassName = $this->getRepositoryShortClassName($class);

        /** @var SmartFileInfo $fileInfo */
        $fileInfo = $class->getAttribute(AttributeKey::FILE_INFO);

        return $fileInfo->getRelativeDirectoryPath() . '/' . $repositoryClassName . '.php';
    }

    /**
     * @param ClassMethod[] $repositoryMethods
     */
    private function createRepositoryClass(Class_ $class, array $repositoryMethods): Class_
    {
        $repositoryClassName = $this->getRepositoryShortClassName($class);
        $repositoryClass = new Class_($repositoryClassName);

        $repositoryClass->stmts[] = $this->doctrineNodeFactory->createRepositoryProperty();

        $entityClass = $this->getName($class->name);
        assert(is_string($entityClass));

        $repositoryClass->stmts[] = $this->doctrineNodeFactory->createConstructorWithGetRepositoryAssign($entityClass);

        foreach ($repositoryMethods as $repositoryMethod) {
            $doctrineRepositoryClassMethod = $this->doctrineRepositoryClassMethodFactory->createFromCakePHPClassMethod(
                $repositoryMethod,
                $entityClass
            );
            $repositoryClass->stmts[] = $doctrineRepositoryClassMethod;
        }

        return $repositoryClass;
    }

    private function getRepositoryShortClassName(Class_ $class): string
    {
        return $this->getShortName($class->name) . 'Repository';
    }
}
