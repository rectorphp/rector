<?php

namespace Rector\Php80\Tests\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class SkipMissingBreak
{
    public function run()
    {
        switch ($this->lexer->lookahead['type']) {
            case Lexer::T_DELETE:
                $statement = $this->DeleteStatement();
        }
    }
}
