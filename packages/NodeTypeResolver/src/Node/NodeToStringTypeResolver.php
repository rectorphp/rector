<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Node;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\PhpParser\Node\Manipulator\ConstFetchManipulator;

final class NodeToStringTypeResolver
{
    /**
     * @var ConstFetchManipulator
     */
    private $constFetchManipulator;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(ConstFetchManipulator $constFetchManipulator, NodeTypeResolver $nodeTypeResolver)
    {
        $this->constFetchManipulator = $constFetchManipulator;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    public function resolver(Node $node): string
    {
        if ($node instanceof LNumber) {
            return 'int';
        }

        if ($node instanceof Array_) {
            return 'mixed[]';
        }

        if ($node instanceof DNumber) {
            return 'float';
        }

        if ($this->nodeTypeResolver->isIntType($node)) {
            return 'int';
        }

        if ($this->nodeTypeResolver->isStringType($node)) {
            return 'string';
        }

        if ($this->constFetchManipulator->isBool($node)) {
            return 'bool';
        }

        return '';
    }
}
