<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Rector;

use PhpParser\Node;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;

abstract class AbstractCakePHPRector extends AbstractRector
{
    protected function isInCakePHPController(Node $node): bool
    {
        if ($this->isObjectType($node, 'AppController')) {
            return true;
        }

        $class = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($class !== null) {
            return true;
        }

        return false;
    }
}
