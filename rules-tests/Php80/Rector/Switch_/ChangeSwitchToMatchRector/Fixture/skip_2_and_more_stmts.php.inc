<?php

namespace Rector\Tests\Php80\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class Skip2AndMoreStmts
{
    public function run()
    {
        switch ($this->lexer->lookahead['type']) {
            case Lexer::T_DELETE:
                $statement = $this->DeleteStatement();
                $statement = $this->DeleteStatement();
            break;
        }
    }
}
