<?php

declare (strict_types=1);
namespace Rector\Php80\ValueObject;

use PhpParser\Node\Expr\Assign;
final class MatchAssignResult
{
    /**
     * @readonly
     * @var \PhpParser\Node\Expr\Assign
     */
    private $assign;
    /**
     * @readonly
     * @var bool
     */
    private $shouldRemovePrevoiusStmt;
    public function __construct(Assign $assign, bool $shouldRemovePrevoiusStmt)
    {
        $this->assign = $assign;
        $this->shouldRemovePrevoiusStmt = $shouldRemovePrevoiusStmt;
    }
    public function getAssign() : Assign
    {
        return $this->assign;
    }
    public function isShouldRemovePreviousStmt() : bool
    {
        return $this->shouldRemovePrevoiusStmt;
    }
}
