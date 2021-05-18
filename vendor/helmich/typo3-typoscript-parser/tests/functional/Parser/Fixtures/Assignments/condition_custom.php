<?php

declare (strict_types=1);
namespace RectorPrefix20210518;

use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ConditionalStatement;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar;
return [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ConditionalStatement('[Foo\\Bar\\Custom = 42]', [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo', 'foo'), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar('bar'), 2)], [], 1)];
