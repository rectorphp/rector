<?php declare(strict_types=1);

namespace Roave\BetterReflection\NodeCompiler;

use PhpParser\Node\Stmt\Property as PropertyNode;

class someClass
{
    /**
     * @var PropertyNode
     */
    private $node;

    /**
     * Get the name of the property.
     *
     * @return string
     */
    public function getName() : string
    {
        return $this->node->props[$this->positionInNode]->name->toString();
    }
}
