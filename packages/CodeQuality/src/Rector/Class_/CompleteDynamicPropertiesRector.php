<?php declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Class_;

use Nette\Utils\Arrays;
use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Class_;
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
        if ($node->isAnonymous()) {
            return null;
        }

        $fetchedLocalPropertyNameToTypes = $this->resolveFetchedLocalPropertyNameToTypes($node);

        $propertyNames = [];
        $this->traverseNodesWithCallable($node->stmts, function (Node $node) use (&$propertyNames) {
            if (! $node instanceof Property) {
                return null;
            }

            $propertyNames[] = $this->getName($node);
        });

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
                $this->docBlockManipulator->changeVarTag($newProperty, $propertyTypesAsString);
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

            $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);

            // fallback type
            $propertyFetchType = ['mixed'];

            // possible get type
            if ($parentNode instanceof Assign) {
                $assignedValueStaticType = $this->getStaticType($parentNode->expr);
                if ($assignedValueStaticType) {
                    $propertyFetchType = $this->typeToStringResolver->resolve($assignedValueStaticType);
                }
            }

            /** @var string $propertyName */
            $propertyName = $this->getName($node->name);

            $fetchedLocalPropertyNameToTypes[$propertyName][] = $propertyFetchType;
        });

        return $fetchedLocalPropertyNameToTypes;
    }
}
