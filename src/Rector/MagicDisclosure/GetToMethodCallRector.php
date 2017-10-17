<?php declare(strict_types=1);

namespace Rector\Rector\MagicDisclosure;

use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use Rector\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * __get to specific call
 */
final class GetToMethodCallRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $typeToMethodCalls = [];

    /**
     * @var PropertyFetchAnalyzer
     */
    private $propertyAccessAnalyzer;

    /**
     * Type to method call()
     *
     * @param string[] $typeToMethodCalls
     */
    public function __construct(array $typeToMethodCalls, PropertyFetchAnalyzer $propertyAccessAnalyzer)
    {
        $this->typeToMethodCalls = $typeToMethodCalls;
        $this->propertyAccessAnalyzer = $propertyAccessAnalyzer;
    }

    public function isCandidate(Node $node): bool
    {
        foreach ($this->typeToMethodCalls as $type => $method) {
            if (! $this->propertyAccessAnalyzer->isPropertyAccessType($node, $type)) {
                continue;
            }

            /** @var PropertyFetch $node */
            return ! $this->propertyAccessAnalyzer->isPropertyAccessOfPublicProperty($node, $type);
        }

        return false;
    }

    /**
     * @param PropertyFetch $propertyFetchNode
     */
    public function refactor(Node $propertyFetchNode): ?Node
    {
        dump($propertyFetchNode);
        die;
    }
}
