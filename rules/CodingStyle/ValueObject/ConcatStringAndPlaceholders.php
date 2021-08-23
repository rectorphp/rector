<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ValueObject;

use PhpParser\Node\Expr;
final class ConcatStringAndPlaceholders
{
    /**
     * @var string
     */
    private $content;
    /**
     * @var \PhpParser\Node\Expr[]
     */
    private $placeholderNodes;
    /**
     * @param Expr[] $placeholderNodes
     */
    public function __construct(string $content, array $placeholderNodes)
    {
        $this->content = $content;
        $this->placeholderNodes = $placeholderNodes;
    }
    public function getContent() : string
    {
        return $this->content;
    }
    /**
     * @return Expr[]
     */
    public function getPlaceholderNodes() : array
    {
        return $this->placeholderNodes;
    }
}
