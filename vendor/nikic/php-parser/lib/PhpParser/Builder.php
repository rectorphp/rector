<?php

declare (strict_types=1);
namespace RectorPrefix20220606\PhpParser;

interface Builder
{
    /**
     * Returns the built node.
     *
     * @return Node The built node
     */
    public function getNode() : Node;
}
