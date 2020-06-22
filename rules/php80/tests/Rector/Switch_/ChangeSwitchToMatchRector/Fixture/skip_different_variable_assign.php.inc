<?php

namespace Rector\Php80\Tests\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class SkipDifferentVariableAssign
{
    public function run()
    {
        switch ($this->lexer->lookahead['type']) {
            case 'a':
                $statementA = $this->DeleteStatement();
            case 'b':
                $statementB = $this->DeleteStatement();
            break;
        }
    }
}
