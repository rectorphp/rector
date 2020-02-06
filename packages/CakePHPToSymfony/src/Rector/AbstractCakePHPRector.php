<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector;

use PhpParser\Node;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;

abstract class AbstractCakePHPRector extends AbstractRector
{
    protected function isInCakePHPController(Node $node): bool
    {
        if ($this->isObjectType($node, 'AppController')) {
            return true;
        }

        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        return $class !== null;
    }
}
