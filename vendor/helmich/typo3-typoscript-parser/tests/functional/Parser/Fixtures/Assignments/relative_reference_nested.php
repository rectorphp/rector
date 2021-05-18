<?php

declare (strict_types=1);
namespace RectorPrefix20210518;

return [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\NestedAssignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo', 'foo'), [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo.bar', 'bar'), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar('baz'), 2), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Reference(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo.baz', 'baz'), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo.bar', '.bar'), 3)], 1)];
