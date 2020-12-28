<?php

namespace Rector\Php80\Tests\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class Fixture
{
    public function run()
    {
        switch ($this->lexer->lookahead['type']) {
            case Lexer::T_DELETE:
                $statement = $this->DeleteStatement();
                break;

            default:
                $statement = $this->syntaxError('SELECT, UPDATE or DELETE');
                break;
        }
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Switch_\ChangeSwitchToMatchRector\Fixture;

class Fixture
{
    public function run()
    {
        $statement = match ($this->lexer->lookahead['type']) {
            Lexer::T_DELETE => $this->DeleteStatement(),
            default => $this->syntaxError('SELECT, UPDATE or DELETE'),
        };
    }
}

?>
