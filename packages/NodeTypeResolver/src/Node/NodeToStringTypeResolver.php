<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Node;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\DNumber;
use PhpParser\Node\Scalar\LNumber;
use Rector\NodeTypeResolver\NodeTypeAnalyzer;
use Rector\PhpParser\Node\Maintainer\ConstFetchMaintainer;

final class NodeToStringTypeResolver
{
    /**
     * @var NodeTypeAnalyzer
     */
    private $nodeTypeAnalyzer;

    /**
     * @var ConstFetchMaintainer
     */
    private $constFetchMaintainer;

    public function __construct(NodeTypeAnalyzer $nodeTypeAnalyzer, ConstFetchMaintainer $constFetchMaintainer)
    {
        $this->nodeTypeAnalyzer = $nodeTypeAnalyzer;
        $this->constFetchMaintainer = $constFetchMaintainer;
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

        if ($this->nodeTypeAnalyzer->isStringType($node)) {
            return 'string';
        }

        if ($this->constFetchMaintainer->isBool($node)) {
            return 'bool';
        }

        return '';
    }
}
