<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser;

use InvalidArgumentException;
use PhpParser\Comment;
use PhpParser\Node;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\NodeDumper;

final class BetterNodeDumper extends NodeDumper
{
    /**
     * @var bool
     */
    private $dumpComments = false;

    /**
     * @var bool
     */
    private $dumpPositions = false;

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
    private $filterAttributes = [];

    /**
     * Constructs a NodeDumper.
     *
     * Supported options:
     *  * bool dumpComments: Whether comments should be dumped.
     *  * bool dumpPositions: Whether line/offset information should be dumped. To dump offset
     *                        information, the code needs to be passed to dump().
     *
     * @param array $options Options (see description)
     */
    public function __construct(array $options = [])
    {
        $this->dumpComments = ! empty($options['dumpComments']);
        $this->dumpPositions = ! empty($options['dumpPositions']);
        parent::__construct($options);
    }

    public function dump($node, ?string $code = null): string
    {
        $this->nodeIds = 0;
        $this->printedNodes = [];
        return parent::dump($node, $code);
    }

    /**
     * @param string[] $filterAttributes
     */
    public function setFilterAttributes(array $filterAttributes): void
    {
        $this->filterAttributes = $filterAttributes;
    }

    protected function dumpRecursive($node)
    {
        if ($node instanceof Node) {
            $r = $this->dumpNode($node);
        } elseif (is_array($node)) {
            $r = $this->dumpArray($node);
        } elseif ($node instanceof Comment) {
            return $node->getReformattedText();
        } else {
            throw new InvalidArgumentException('Can only dump nodes and arrays.');
        }

        return $r;
    }

    /**
     * @return mixed[]
     */
    private function getFilteredAttributes(Node $node) : array
    {
        $attributes = $node->getAttributes();
        if ($this->filterAttributes !== []) {
            $attributes = array_intersect_key($attributes, array_flip($this->filterAttributes));
        }

        return $attributes;
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

        if ($this->dumpPositions ) {
            $r .= $this->dumpPosition($node);
        }
        $r .= '(';

        $r .= $this->dumpSubNodes($node);

        $comments = $node->getComments();
        if ($this->dumpComments && $comments) {
            $r .= "\n    comments: " . str_replace("\n", "\n    ", $this->dumpRecursive($comments));
        }
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
            } elseif (!$value) {
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
