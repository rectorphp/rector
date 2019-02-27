<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Node;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use Rector\NodeTypeResolver\NodeTypeAnalyzer;
use Rector\PhpParser\Node\Manipulator\ConstFetchManipulator;

final class NodeToStringTypeResolver
{
    /**
     * @var NodeTypeAnalyzer
     */
    private $nodeTypeAnalyzer;

    /**
     * @var ConstFetchManipulator
     */
    private $constFetchManipulator;

    public function __construct(NodeTypeAnalyzer $nodeTypeAnalyzer, ConstFetchManipulator $constFetchManipulator)
    {
        $this->nodeTypeAnalyzer = $nodeTypeAnalyzer;
        $this->constFetchManipulator = $constFetchManipulator;
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

        if ($this->nodeTypeAnalyzer->isIntType($node)) {
            return 'int';
        }

        if ($this->nodeTypeAnalyzer->isStringType($node)) {
            return 'string';
        }

        if ($this->constFetchManipulator->isBool($node)) {
            return 'bool';
        }

        return '';
    }
}
