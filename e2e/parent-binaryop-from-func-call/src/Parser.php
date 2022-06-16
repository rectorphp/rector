<?php

final class Parser
{
    private function parseBlock()
    {
        $item = new stdClass;
        $indent = $nextIndent = $res = '';

        loop:
            if ($this->tokens->consume(Token::Newline)) {
                while ($this->tokens->consume(Token::Newline));
                $nextIndent = $this->tokens->getIndentation();

                if (strncmp($nextIndent, $indent, min(strlen($nextIndent), strlen($indent)))) {
                    $this->tokens->error('Invalid combination of tabs and spaces');
                } elseif (strlen($nextIndent) > strlen($indent)) {
                    $item->value = $this->parseBlock($nextIndent);
                }
            }

            $nextIndent = $this->tokens->getIndentation();
            if (strncmp($nextIndent, $indent, min(strlen($nextIndent), strlen($indent)))) {
                $this->tokens->error('Invalid combination of tabs and spaces');
            }

        goto loop;
    }
}
