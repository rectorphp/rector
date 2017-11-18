<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Rector\AbstractRector;

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
     * @var string[]
     */
    private $activeTypes = [];

    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;

    /**
     * @param string[][][]
     */
    public function __construct(array $perClassOldToNewProperties, PropertyFetchAnalyzer $propertyFetchAnalyzer)
    {
        $this->perClassPropertyToMethods = $perClassOldToNewProperties;
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof PropertyFetch) {
            return false;
        }

        foreach ($this->perClassPropertyToMethods as $class => $propertyToMethods) {
            if ($this->propertyFetchAnalyzer->isTypeAndProperties($node, $class, array_keys($propertyToMethods))) {
                $this->activeTypes = $propertyToMethods[$node->name->toString()];

                return true;
            }
        }

        return false;
    }

    /**
     * @param PropertyFetch $propertyFetchNode
     */
    public function refactor(Node $propertyFetchNode): ?Node
    {
        return new MethodCall(
            $propertyFetchNode->var,
            $this->activeTypes[0]
        );
    }
}
