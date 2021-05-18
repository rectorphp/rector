<?php

declare (strict_types=1);
namespace RectorPrefix20210518;

return [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\NestedAssignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath("foo", "foo"), [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath("foo.0", "0"), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar("hello"), 2), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath("foo.1", "1"), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar("world"), 3)], 1)];
