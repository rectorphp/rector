<?php

declare (strict_types=1);
namespace RectorPrefix20210518;

return [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ConditionalStatement('[globalVar = GP:foo=1]', [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo', 'foo'), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar('bar'), 2)], [], 1), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ConditionalStatement('[globalVar = GP:foo=2]', [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo', 'foo'), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar('bar2'), 4)], [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo', 'foo'), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar('baz'), 6)], 3)];
