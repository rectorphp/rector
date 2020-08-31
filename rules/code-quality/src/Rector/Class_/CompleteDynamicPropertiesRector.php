<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use PhpParser\NodeTraverser;
use PHPStan\Type\MixedType;
use PHPStan\Type\Type;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;

/**
 * @see https://3v4l.org/GL6II
 * @see https://3v4l.org/eTrhZ
 * @see https://3v4l.org/C554W
 *
 * @see \Rector\CodeQuality\Tests\Rector\Class_\CompleteDynamicPropertiesRector\CompleteDynamicPropertiesRectorTest
 */
final class CompleteDynamicPropertiesRector extends AbstractRector
{
    /**
     * @var string
     */
    private const LARAVEL_COLLECTION_CLASS = 'Illuminate\Support\Collection';

    /**
     * @var TypeFactory
     */
    private $typeFactory;

    public function __construct(TypeFactory $typeFactory)
    {
        $this->typeFactory = $typeFactory;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add missing dynamic properties', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function set()
    {
        $this->value = 5;
    }
}
PHP
                ,
                <<<'PHP'
class SomeClass
{
    /**
     * @var int
     */
    public $value;
    public function set()
    {
        $this->value = 5;
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
        if (! $this->isNonAnonymousClass($node)) {
            return null;
        }

        /** @var string $class */
        $class = $this->getName($node);

        // properties are accessed via magic, nothing we can do
        if (method_exists($class, '__set') || method_exists($class, '__get')) {
            return null;
        }

        // special case for Laravel Collection macro magic
        $fetchedLocalPropertyNameToTypes = $this->resolveFetchedLocalPropertyNameToType($node);

        $propertiesToComplete = $this->resolvePropertiesToComplete($node, $fetchedLocalPropertyNameToTypes);
        if ($propertiesToComplete === []) {
            return null;
        }

        // remove other properties that are accessible from this scope
        /** @var string $class */
        $class = $this->getName($node);

        foreach ($propertiesToComplete as $key => $propertyToComplete) {
            /** @var string $propertyToComplete */
            if (! property_exists($class, $propertyToComplete)) {
                continue;
            }

            unset($propertiesToComplete[$key]);
        }

        $newProperties = $this->createNewProperties($fetchedLocalPropertyNameToTypes, $propertiesToComplete);

        $node->stmts = array_merge($newProperties, $node->stmts);

        return $node;
    }

    /**
     * @return array<string, \PHPStan\Type\Type>
     */
    private function resolveFetchedLocalPropertyNameToType(Class_ $class): array
    {
        $fetchedLocalPropertyNameToTypes = $this->resolveFetchedLocalPropertyNamesToTypes($class);

        // normalize types to union
        $fetchedLocalPropertyNameToType = [];
        foreach ($fetchedLocalPropertyNameToTypes as $name => $types) {
            $fetchedLocalPropertyNameToType[$name] = $this->typeFactory->createMixedPassedOrUnionType($types);
        }

        return $fetchedLocalPropertyNameToType;
    }

    /**
     * @param array<string, Type> $fetchedLocalPropertyNameToTypes
     * @return array<int, string>
     */
    private function resolvePropertiesToComplete(Class_ $class, array $fetchedLocalPropertyNameToTypes): array
    {
        $propertyNames = $this->getClassPropertyNames($class);

        /** @var string[] $fetchedLocalPropertyNames */
        $fetchedLocalPropertyNames = array_keys($fetchedLocalPropertyNameToTypes);

        return array_diff($fetchedLocalPropertyNames, $propertyNames);
    }

    /**
     * @param Type[] $fetchedLocalPropertyNameToTypes
     * @param string[] $propertiesToComplete
     * @return Property[]
     */
    private function createNewProperties(array $fetchedLocalPropertyNameToTypes, array $propertiesToComplete): array
    {
        $newProperties = [];
        foreach ($fetchedLocalPropertyNameToTypes as $propertyName => $propertyType) {
            if (! in_array($propertyName, $propertiesToComplete, true)) {
                continue;
            }

            $property = $this->nodeFactory->createPublicProperty($propertyName);

            // fallback to doc type in PHP 7.4
            /** @var PhpDocInfo $phpDocInfo */
            $phpDocInfo = $property->getAttribute(AttributeKey::PHP_DOC_INFO);

            if ($this->isAtLeastPhpVersion(PhpVersionFeature::TYPED_PROPERTIES)) {
                $phpStanNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($propertyType);
                if ($phpStanNode !== null) {
                    $property->type = $phpStanNode;
                } else {
                    // fallback to doc type in PHP 7.4
                    $phpDocInfo->changeVarType($propertyType);
                }
            } else {
                $phpDocInfo->changeVarType($propertyType);
            }

            $newProperties[] = $property;
        }

        return $newProperties;
    }

    /**
     * @return array<string, \PHPStan\Type\Type[]>
     */
    private function resolveFetchedLocalPropertyNamesToTypes(Class_ $class): array
    {
        $fetchedLocalPropertyNameToTypes = [];

        $this->traverseNodesWithCallable($class->stmts, function (Node $node) use (
            &$fetchedLocalPropertyNameToTypes
        ): ?int {
            // skip anonymous class scope
            if ($this->isAnonymousClass($node)) {
                return NodeTraverser::DONT_TRAVERSE_CHILDREN;
            }

            if (! $node instanceof PropertyFetch) {
                return null;
            }

            if (! $this->isVariableName($node->var, 'this')) {
                return null;
            }

            // special Laravel collection scope
            if ($this->shouldSkipForLaravelCollection($node)) {
                return null;
            }

            $propertyName = $this->getName($node->name);
            if ($propertyName === null) {
                return null;
            }

            $propertyFetchType = $this->resolvePropertyFetchType($node);

            $fetchedLocalPropertyNameToTypes[$propertyName][] = $propertyFetchType;

            return null;
        });

        return $fetchedLocalPropertyNameToTypes;
    }

    /**
     * @return string[]
     */
    private function getClassPropertyNames(Class_ $class): array
    {
        $propertyNames = [];

        foreach ($class->getProperties() as $property) {
            $propertyNames[] = $this->getName($property);
        }

        return $propertyNames;
    }

    private function shouldSkipForLaravelCollection(Node $node): bool
    {
        $staticCallOrClassMethod = $this->betterNodeFinder->findFirstAncestorInstancesOf(
            $node,
            [ClassMethod::class, StaticCall::class]
        );

        if (! $staticCallOrClassMethod instanceof StaticCall) {
            return false;
        }

        return $this->isName($staticCallOrClassMethod->class, self::LARAVEL_COLLECTION_CLASS);
    }

    private function resolvePropertyFetchType(Node $node): Type
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);

        // possible get type
        if ($parentNode instanceof Assign) {
            return $this->getStaticType($parentNode->expr);
        }

        return new MixedType();
    }
}
