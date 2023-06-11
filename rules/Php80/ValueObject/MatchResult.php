<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObject;

use PhpParser\Node\Expr\Match_;
final class MatchResult
{
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\Match_
     */
    private $match;
    /**
     * @readonly
     * @var bool
     */
    private $shouldRemoveNextStmt;
    public function __construct(Match_ $match, bool $shouldRemoveNextStmt)
    {
        $this->match = $match;
        $this->shouldRemoveNextStmt = $shouldRemoveNextStmt;
    }
    public function getMatch() : Match_
    {
        return $this->match;
    }
    public function shouldRemoveNextStmt() : bool
    {
        return $this->shouldRemoveNextStmt;
    }
}
