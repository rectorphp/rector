<?php

declare(strict_types=1);

namespace Rector\PhpParser;

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
    private $dumpComments;

    private $dumpPositions;

    private $printedNodes = [];

    private $nodeIds = 0;

    private $skipEmpty = true;

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
     * @param mixed $attribute
     */
    protected function dumpAttributeValue($attribute): string
    {
        if ($attribute === null) {
            return 'null';
        } elseif ($attribute === false) {
            return 'false';
        } elseif ($attribute === true) {
            return 'true';
        } elseif (is_scalar($attribute)) {
            return (string) $attribute;
        }
        return str_replace("\n", "\n    ", $this->dumpRecursive($attribute));
    }

    /**
     * @return mixed[]
     */
    protected function getFilteredAttributes(Node $node) : array
    {
        $attributes = $node->getAttributes();
        if ($this->filterAttributes !== []) {
            $attributes = array_intersect_key($attributes, array_flip($this->filterAttributes));
        }
        return $attributes;
    }

    protected function dumpSubNodes(Node $node): string
    {
        $r = '';
        foreach ($node->getSubNodeNames() as $key) {
            $value = $node->{$key};

            if ($this->skipEmpty && ($value === null || $value === [])) {
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

    protected function dumpNode(Node $node): string
    {
        $spl_object_hash = spl_object_hash($node);

        if (isset($this->printedNodes[$spl_object_hash])) {
            return $node->getType() . ' #' . $this->printedNodes[$spl_object_hash];
        }
        $this->printedNodes[$spl_object_hash] = $this->nodeIds++;
        $r = $node->getType() . ' #' . $this->printedNodes[$spl_object_hash];

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
