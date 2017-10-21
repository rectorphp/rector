<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use Rector\NodeAnalyzer\ClassConstAnalyzer;
use Rector\Rector\AbstractRector;

/**
 * Useful when parent class or interface gets new typehints,
 * that breaks contract with child instances.
 *
 * E.g. interface SomeInterface
 * {
 *      public read($content);
 * }
 *
 * After
 *      public read(string $content);
 */

final class ParentTypehintedArgumentRector extends AbstractRector
{
    /**
     * class => [
     *      method => [
     *           argument => typehting
     *      ]
     * ]
     *
     * @var string[]
     */
    private $typehintForArgumentByMethodAndClass = [];

    /**
     * @param mixed[] $typehintForArgumentByMethodAndClass
     */
    public function __construct(array $typehintForArgumentByMethodAndClass)
    {
        $this->typehintForArgumentByMethodAndClass = $typehintForArgumentByMethodAndClass;
    }

    public function isCandidate(Node $node): bool
    {
        dump($node);
        die;
        // ... method -> parmaeters -> parent
    }

    /**
     * @param ClassConstFetch $classConstFetchNode
     */
    public function refactor(Node $classConstFetchNode): ?Node
    {
    }
}
