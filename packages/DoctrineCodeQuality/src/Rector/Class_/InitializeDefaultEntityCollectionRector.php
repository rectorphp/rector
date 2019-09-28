<?php declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use Rector\BetterPhpDocParser\Contract\Doctrine\ToManyTagNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_\EntityTagValueNode;
use Rector\Doctrine\ValueObject\DoctrineClass;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://www.doctrine-project.org/projects/doctrine-orm/en/2.6/reference/best-practices.html#initialize-collections-in-the-constructor
 *
 * @see \Rector\DoctrineCodeQuality\Tests\Rector\Class_\InitializeDefaultEntityCollectionRector\InitializeDefaultEntityCollectionRectorTest
 */
final class InitializeDefaultEntityCollectionRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Initialize collection property in Entity constructor', [
            new CodeSample(
                <<<'PHP'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\OneToMany(targetEntity="MarketingEvent")
     */
    private $marketingEvents = [];
}
PHP
                ,
                <<<'PHP'
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\OneToMany(targetEntity="MarketingEvent")
     */
    private $marketingEvents = [];

    public function __construct()
    {
        $this->marketingEvents = new ArrayCollection();
    }
}
PHP
            ),
        ]);
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
        $classPhpDocInfo = $this->getPhpDocInfo($node);
        if ($classPhpDocInfo === null) {
            return null;
        }

        if (! $classPhpDocInfo->getByType(EntityTagValueNode::class)) {
            return null;
        }

        $toManyPropertyNames = $this->resolveToManyPropertyNames($node);
        if ($toManyPropertyNames === []) {
            return null;
        }

        $constructClassMethod = $node->getMethod('__construct');
        $assigns = $this->createAssignsOfArrayCollectionsForPropertyNames($toManyPropertyNames);

        if ($constructClassMethod === null) {
            $constructClassMethod = $this->nodeFactory->createPublicMethod('__construct');
            $constructClassMethod->stmts = $assigns;

            $node->stmts = array_merge((array) $node->stmts, [$constructClassMethod]);
        } else {
            $assigns = $this->filterOutExistingAssigns($constructClassMethod, $assigns);

            // all properties are initialized → skip
            if ($assigns === []) {
                return null;
            }

            $constructClassMethod->stmts = array_merge($assigns, (array) $constructClassMethod->stmts);
        }

        return $node;
    }

    /**
     * @return string[]
     */
    private function resolveToManyPropertyNames(Class_ $class): array
    {
        $collectionPropertyNames = [];

        foreach ($class->getProperties() as $property) {
            if (count($property->props) !== 1) {
                continue;
            }

            $propertyPhpDocInfo = $this->getPhpDocInfo($property);
            if ($propertyPhpDocInfo === null) {
                continue;
            }

            if (! $propertyPhpDocInfo->getByType(ToManyTagNodeInterface::class)) {
                continue;
            }

            /** @var string $propertyName */
            $propertyName = $this->getName($property);

            $collectionPropertyNames[] = $propertyName;
        }

        return $collectionPropertyNames;
    }

    /**
     * @param string[] $propertyNames
     * @return Expression[]
     */
    private function createAssignsOfArrayCollectionsForPropertyNames(array $propertyNames): array
    {
        $assigns = [];
        foreach ($propertyNames as $propertyName) {
            $assigns[] = $this->createPropertyArrayCollectionAssign($propertyName);
        }

        return $assigns;
    }

    private function createPropertyArrayCollectionAssign(string $toManyPropertyName): Expression
    {
        $propertyFetch = $this->createPropertyFetch('this', $toManyPropertyName);
        $newCollection = new New_(new FullyQualified(DoctrineClass::ARRAY_COLLECTION));

        $assign = new Assign($propertyFetch, $newCollection);

        return new Expression($assign);
    }

    /**
     * @param Expression[] $assigns
     * @return Expression[]
     */
    private function filterOutExistingAssigns(Node\Stmt\ClassMethod $constructClassMethod, array $assigns): array
    {
        $this->traverseNodesWithCallable((array) $constructClassMethod->stmts, function (Node $node) use (&$assigns) {
            foreach ($assigns as $key => $assign) {
                if (! $this->areNodesEqual($node, $assign)) {
                    continue;
                }

                unset($assigns[$key]);
            }

            return null;
        });

        return $assigns;
    }
}
