<?php

declare (strict_types=1);
namespace RectorPrefix20210518\Helmich\TypoScriptParser\Tests\Unit\Parser\AST;

use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath;
use RectorPrefix20210518\PHPUnit\Framework\TestCase;
class ObjectPathTest extends \RectorPrefix20210518\PHPUnit\Framework\TestCase
{
    public function testPathsRemainStrings()
    {
        $op = new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath("foo.0", "0");
        assertThat($op->relativeName, identicalTo("0"));
    }
    public function testIntPathsRaisesTypeError()
    {
        $this->expectException(\TypeError::class);
        new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath("foo.0", 0);
    }
}
