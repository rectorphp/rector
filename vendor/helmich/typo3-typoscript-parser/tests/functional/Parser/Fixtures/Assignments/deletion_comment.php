<?php

declare (strict_types=1);
namespace RectorPrefix20210518;

use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Comment;
return [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Delete(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo', 'foo'), 1), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Comment('# Something', 1)];
