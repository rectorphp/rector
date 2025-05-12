<?php

declare (strict_types=1);
namespace Rector\Doctrine\Bundle230\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\Comments\NodeDocBlock\DocBlockUpdater;
use Rector\Doctrine\Enum\DoctrineClass;
use Rector\Rector\AbstractRector;
use Rector\ValueObject\MethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/doctrine/DoctrineBundle/commit/2f12b5302bafac39c70b024e1686119be28b79ab
 *
 * @see \Rector\Doctrine\Tests\Bundle230\Rector\Class_\AddAnnotationToRepositoryRector\AddAnnotationToRepositoryRectorTest
 */
final class AddAnnotationToRepositoryRector extends AbstractRector
{
    /**
     * @readonly
     */
    private DocBlockUpdater $docBlockUpdater;
    /**
     * @readonly
     */
    private PhpDocInfoFactory $phpDocInfoFactory;
    public function __construct(DocBlockUpdater $docBlockUpdater, PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->docBlockUpdater = $docBlockUpdater;
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Add @extends ServiceEntityRepository<T> annotation to repository classes', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

final class SomeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SomeEntity::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @extends ServiceEntityRepository<\SomeEntity>
 */
final class SomeRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SomeEntity::class);
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
        if (!$this->isRepositoryClass($node)) {
            return null;
        }
        $entityClass = $this->getEntityClassFromConstructor($node);
        if ($entityClass === null || $this->hasExtendsAnnotation($node)) {
            return null;
        }
        $this->addAnnotationToNode($node, $entityClass);
        return $node;
    }
    private function isRepositoryClass(Class_ $class) : bool
    {
        if (!$class->extends instanceof Name) {
            return \false;
        }
        return $this->isName($class->extends, DoctrineClass::SERVICE_ENTITY_REPOSITORY);
    }
    private function getEntityClassFromConstructor(Class_ $class) : ?string
    {
        $classMethod = $class->getMethod(MethodName::CONSTRUCT);
        if (!$classMethod instanceof ClassMethod || $classMethod->stmts === null) {
            return null;
        }
        foreach ($classMethod->stmts as $stmt) {
            if (!$stmt instanceof Expression) {
                continue;
            }
            $expr = $stmt->expr;
            if (!$expr instanceof StaticCall) {
                continue;
            }
            if (!$this->isParentConstructorCall($expr)) {
                continue;
            }
            $entityClassNode = $expr->args[1]->value ?? null;
            if (!$entityClassNode instanceof ClassConstFetch) {
                continue;
            }
            $entityClass = $entityClassNode->class;
            return $entityClass instanceof Name ? $entityClass->toString() : null;
        }
        return null;
    }
    private function addAnnotationToNode(Class_ $class, string $entityClass) : void
    {
        $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($class);
        $genericsAnnotation = \sprintf('\\%s<\\%s>', DoctrineClass::SERVICE_ENTITY_REPOSITORY, $entityClass);
        $phpDocInfo->addPhpDocTagNode(new PhpDocTagNode('@extends', new GenericTagValueNode($genericsAnnotation)));
        $this->docBlockUpdater->updateRefactoredNodeWithPhpDocInfo($class);
    }
    private function hasExtendsAnnotation(Class_ $class) : bool
    {
        return $this->phpDocInfoFactory->createFromNodeOrEmpty($class)->hasByName('@extends');
    }
    private function isParentConstructorCall(StaticCall $staticCall) : bool
    {
        return $this->isName($staticCall->class, 'parent') && $this->isName($staticCall->name, '__construct') && isset($staticCall->args[1]) && $staticCall->args[1] instanceof Arg && $staticCall->args[1]->value instanceof ClassConstFetch;
    }
}
