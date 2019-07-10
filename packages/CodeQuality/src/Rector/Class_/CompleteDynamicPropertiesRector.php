<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Class_;

use Nette\Utils\Arrays;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\NodeTypeResolver\PHPStan\Type\TypeToStringResolver;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see https://3v4l.org/GL6II
 * @see https://3v4l.org/eTrhZ
 * @see https://3v4l.org/C554W
 */
final class CompleteDynamicPropertiesRector extends AbstractRector
{
    /**
     * @var string
     */
    private const LARAVEL_COLLECTION_CLASS = 'Illuminate\Support\Collection';

    /**
     * @var TypeToStringResolver
     */
    private $typeToStringResolver;

    /**
     * @var DocBlockManipulator
     */
    private $docBlockManipulator;

    public function __construct(
        TypeToStringResolver $typeToStringResolver,
        DocBlockManipulator $docBlockManipulator
    ) {
        $this->typeToStringResolver = $typeToStringResolver;
        $this->docBlockManipulator = $docBlockManipulator;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add missing dynamic properties', [
            new CodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function set()
    {
        $this->value = 5;
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
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
CODE_SAMPLE
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
        $fetchedLocalPropertyNameToTypes = $this->resolveFetchedLocalPropertyNameToTypes($node);

        $propertyNames = $this->getClassPropertyNames($node);

        $fetchedLocalPropertyNames = array_keys($fetchedLocalPropertyNameToTypes);
        $propertiesToComplete = array_diff($fetchedLocalPropertyNames, $propertyNames);

        // remove other properties that are accessible from this scope
        /** @var string $class */
        $class = $this->getName($node);
        foreach ($propertiesToComplete as $key => $propertyToComplete) {
            if (! property_exists($class, $propertyToComplete)) {
                continue;
            }

            unset($propertiesToComplete[$key]);
        }

        $newProperties = $this->createNewProperties($fetchedLocalPropertyNameToTypes, $propertiesToComplete);

        $node->stmts = array_merge_recursive($newProperties, $node->stmts);

        return $node;
    }

    /**
     * @param string[][][] $fetchedLocalPropertyNameToTypes
     * @param string[] $propertiesToComplete
     * @return Property[]
     */
    private function createNewProperties(array $fetchedLocalPropertyNameToTypes, array $propertiesToComplete): array
    {
        $newProperties = [];
        foreach ($fetchedLocalPropertyNameToTypes as $propertyName => $propertyTypes) {
            if (! in_array($propertyName, $propertiesToComplete, true)) {
                continue;
            }

            $propertyTypes = Arrays::flatten($propertyTypes);
            $propertyTypesAsString = implode('|', $propertyTypes);

            $propertyBuilder = $this->builderFactory->property($propertyName)
                ->makePublic();

            if ($this->isAtLeastPhpVersion('7.4') && count($propertyTypes) === 1) {
                $newProperty = $propertyBuilder->setType($propertyTypes[0])
                    ->getNode();
            } else {
                $newProperty = $propertyBuilder->getNode();
                if ($propertyTypesAsString) {
                    $this->docBlockManipulator->changeVarTag($newProperty, $propertyTypesAsString);
                }
            }

            $newProperties[] = $newProperty;
        }
        return $newProperties;
    }

    /**
     * @return string[][][]
     */
    private function resolveFetchedLocalPropertyNameToTypes(Class_ $class): array
    {
        $fetchedLocalPropertyNameToTypes = [];

        $this->traverseNodesWithCallable($class->stmts, function (Node $node) use (
            &$fetchedLocalPropertyNameToTypes
        ) {
            if (! $node instanceof PropertyFetch) {
                return null;
            }

            if (! $this->isName($node->var, 'this')) {
                return null;
            }

            // special Laravel collection scope
            if ($this->shouldSkipForLaravelCollection($node)) {
                return null;
            }

            /** @var string $propertyName */
            $propertyName = $this->getName($node->name);
            $propertyFetchType = $this->resolvePropertyFetchType($node);

            $fetchedLocalPropertyNameToTypes[$propertyName][] = $propertyFetchType;
        });

        return $fetchedLocalPropertyNameToTypes;
    }

    /**
     * @return string[]
     */
    private function getClassPropertyNames(Class_ $class): array
    {
        $propertyNames = [];

        $this->traverseNodesWithCallable($class->stmts, function (Node $node) use (&$propertyNames) {
            if (! $node instanceof Property) {
                return null;
            }

            $propertyNames[] = $this->getName($node);
        });

        return $propertyNames;
    }

    /**
     * @return string[]
     */
    private function resolvePropertyFetchType(Node $node): array
    {
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);

        // possible get type
        if ($parentNode instanceof Assign) {
            $assignedValueStaticType = $this->getStaticType($parentNode->expr);
            if ($assignedValueStaticType) {
                return $this->typeToStringResolver->resolve($assignedValueStaticType);
            }
        }

        // fallback type
        return ['mixed'];
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
}
