<?php

declare (strict_types=1);
namespace Rector\Php55\NodeVisitor;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeVisitorAbstract;
final class ClassConstStringValueNodeVisitor extends NodeVisitorAbstract
{
    /**
     * @var string
     */
    private const IS_UNDER_CLASS_CONST = 'is_under_class_const';
    public function enterNode(Node $node)
    {
        if ($node instanceof String_) {
            $node->setAttribute(self::IS_UNDER_CLASS_CONST, \true);
        }
        return null;
    }
}
