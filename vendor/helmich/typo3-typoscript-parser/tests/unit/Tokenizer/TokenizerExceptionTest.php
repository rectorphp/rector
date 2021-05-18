<?php

namespace RectorPrefix20210518\Helmich\TypoScriptParser\Tests\Unit\Tokenizer;

use RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\TokenizerException;
use RectorPrefix20210518\PHPUnit\Framework\TestCase;
class TokenizerExceptionTest extends \RectorPrefix20210518\PHPUnit\Framework\TestCase
{
    public function testCanGetSourceLine()
    {
        $exc = new \RectorPrefix20210518\Helmich\TypoScriptParser\Tokenizer\TokenizerException('Foobar', 1234, null, 4312);
        assertThat($exc->getSourceLine(), equalTo(4312));
    }
}
