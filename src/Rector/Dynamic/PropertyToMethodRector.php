<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Identifier;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Example - from:
 * - $result = $object->property;
 * - $object->property = $value;
 *
 * To
 * - $result = $object->getProperty();
 * - $object->setProperty($value);
 */
final class PropertyToMethodRector extends AbstractRector
{
    /**
     * class => [
     *     property => [getMethod, setMethod]
     * ]
     *
     * @var string[][][]
     */
    private $perClassPropertyToMethods = [];

    /**
     * @var string
     */
    private $activeMethod;

    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    /**
     * @param string[][][] $perClassOldToNewProperties
     */
    public function __construct(array $perClassOldToNewProperties, PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->perClassPropertyToMethods = $perClassOldToNewProperties;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof Assign) {
            return false;
        }

        // setter
        if ($node->var instanceof PropertyFetch) {
            return $this->processPropertyFetchCandidate($node->var, 'set');
        }

        // getter
        if ($node->expr instanceof PropertyFetch) {
            return $this->processPropertyFetchCandidate($node->expr, 'get');
        }

        return false;
    }

    /**
     * @param Assign $assignNode
     */
    public function refactor(Node $assignNode): ?Node
    {
        // setter
        if ($assignNode->var instanceof PropertyFetch) {
            $args = [new Arg($assignNode->expr)];

            return new MethodCall($assignNode->var->var, $this->activeMethod, $args);
        }

        // getter
        if ($assignNode->expr instanceof PropertyFetch) {
            $assignNode->expr = new MethodCall($assignNode->expr->var, $this->activeMethod);
        }

        return null;
    }

    private function processPropertyFetchCandidate(PropertyFetch $propertyFetchNode, string $type): bool
    {
        foreach ($this->perClassPropertyToMethods as $class => $propertyToMethods) {
            $properties = array_keys($propertyToMethods);
            if ($this->propertyFetchAnalyzer->isTypeAndProperties($propertyFetchNode, $class, $properties)) {
                /** @var Identifier $identifierNode */
                $identifierNode = $propertyFetchNode->name;

                $this->activeMethod = $propertyToMethods[$identifierNode->toString()][$type];

                return true;
            }
        }

        return false;
    }
}
