<?php

declare(strict_types=1);

namespace Rector\Generic\Rector;

use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\ClassConstFetch;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Renaming\ValueObject\MethodCallRename;

final class ReplaceArrayWithObjectRector extends AbstractRector
{
    public function getNodeTypes(): array
    {
        return [ArrayItem::class];
    }

    /**
     * @param ArrayItem $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $node->key instanceof ClassConstFetch) {
            return null;
        }

        if (! $this->isValue($node->key, 'Rector\Renaming\Rector\MethodCall\RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS')) {
            return null;
        }

        // already converted
        if ($node->value instanceof FuncCall) {
            return null;
        }

        if (! $node->value instanceof Array_) {
            return null;
        }

        foreach ($node->value->items as $key => $nestedArrayItem) {
            if (! $nestedArrayItem instanceof ArrayItem) {
                throw new ShouldNotHappenException();
            }

            if (! $nestedArrayItem->key instanceof Node\Scalar\String_) {
                throw new ShouldNotHappenException();
            }

            if ($nestedArrayItem->value instanceof Array_) {
                foreach ($nestedArrayItem->value->items as $nestedNestedArrayItem) {
                    $ars = [
                        $nestedArrayItem->key,
                        $nestedNestedArrayItem->key,
                        $nestedNestedArrayItem->value
                    ];

                    $node->value->items[] = new Node\Expr\New_(new Node\Name\FullyQualified(MethodCallRename::class), $ars);
                }

                unset($node->value->items[$key]);
            }
        }

        return null;
    }

    public function getDefinition(): RectorDefinition
    {

    }
}
