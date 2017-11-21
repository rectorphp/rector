<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use Rector\Node\Attribute;
use Rector\Rector\AbstractRector;

final class ValueObjectRemoverRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $oldValueObjects = [];

    /**
     * @param string[] $oldValueObjects
     */
    public function __construct(array $oldValueObjects)
    {
        $this->oldValueObjects = $oldValueObjects;
    }

    public function isCandidate(Node $node): bool
    {
        if (! $node instanceof New_) {
            return false;
        }

        if (count($node->args) !== 1) {
            return false;
        }

        $classNodeTypes = $node->class->getAttribute(Attribute::TYPES);

        return (bool) array_intersect($classNodeTypes, $this->oldValueObjects);
    }

    /**
     * @param New_ $newNode
     */
    public function refactor(Node $newNode): ?Node
    {
        return $newNode->args[0];
    }
}
