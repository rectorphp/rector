<?php

declare(strict_types=1);

namespace Rector\Core\Testing\Dumper;

use InvalidArgumentException;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeDumper;

final class AttributeFilteringNodeDumper extends NodeDumper
{
    /**
     * @var int[]
     */
    private $printedNodes = [];

    /**
     * @var int
     */
    private $nodeIds = 0;

    /**
     * @var string[]
     */
    private $relevantAttributes = [];

    /**
     * @param string[] $relevantAttributes
     */
    public function __construct(array $relevantAttributes)
    {
        $this->relevantAttributes = $relevantAttributes;
    }

    public function dump($node, ?string $code = null): string
    {
        $this->nodeIds = 0;
        $this->printedNodes = [];

        return parent::dump($node, $code);
    }

    protected function dumpRecursive($node)
    {
        if ($node instanceof Node) {
            return $this->dumpNode($node);
        } elseif (is_array($node)) {
            return $this->dumpArray($node);
        } elseif ($node instanceof Comment) {
            return $node->getReformattedText();
        }

        throw new InvalidArgumentException('Can only dump nodes and arrays.');
    }

    /**
     * @return mixed[]
     */
    private function getFilteredAttributes(Node $node): array
    {
        $attributes = $node->getAttributes();
        return array_intersect_key($attributes, array_flip($this->relevantAttributes));
    }

    private function dumpSubNodes(Node $node): string
    {
        $r = '';
        foreach ($node->getSubNodeNames() as $key) {
            $value = $node->{$key};

            if (($value === null || $value === [])) {
                continue;
            }

            $r .= "\n    " . $key . ': ';

            if ($value === null) {
                $r .= 'null';
            } elseif ($value === false) {
                $r .= 'false';
            } elseif ($value === true) {
                $r .= 'true';
            } elseif (is_scalar($value)) {
                if ($key === 'flags' || $key === 'newModifier') {
                    $r .= $this->dumpFlags($value);
                } elseif ($key === 'type' && $node instanceof Include_) {
                    $r .= $this->dumpIncludeType($value);
                } elseif ($key === 'type'
                    && ($node instanceof Use_ || $node instanceof UseUse || $node instanceof GroupUse)) {
                    $r .= $this->dumpUseType($value);
                } else {
                    $r .= $value;
                }
            } else {
                $r .= str_replace("\n", "\n    ", $this->dumpRecursive($value));
            }
        }
        return $r;
    }

    private function dumpNode(Node $node): string
    {
        $splObjectHash = spl_object_hash($node);

        if (isset($this->printedNodes[$splObjectHash])) {
            return $node->getType() . ' #' . $this->printedNodes[$splObjectHash];
        }

        $this->printedNodes[$splObjectHash] = $this->nodeIds++;
        $r = $node->getType() . ' #' . $this->printedNodes[$splObjectHash];

        $r .= '(';

        $r .= $this->dumpSubNodes($node);

        $attributes = $this->getFilteredAttributes($node);
        if ($attributes !== []) {
            $r .= "\n    attributes: " . str_replace("\n", "\n    ", $this->dumpArray($attributes));
        }
        return $r . "\n)";
    }

    /**
     * @param mixed[] $node
     */
    private function dumpArray(array $node): string
    {
        $r = 'array(';

        foreach ($node as $key => $value) {
            $r .= "\n    " . $key . ': ';

            if ($value === null) {
                $r .= 'null';
            } elseif (! $value) {
                $r .= 'false';
            } elseif ($value === true) {
                $r .= 'true';
            } elseif (is_scalar($value)) {
                $r .= $value;
            } else {
                $r .= str_replace("\n", "\n    ", $this->dumpRecursive($value));
            }
        }
        return $r . "\n)";
    }
}
