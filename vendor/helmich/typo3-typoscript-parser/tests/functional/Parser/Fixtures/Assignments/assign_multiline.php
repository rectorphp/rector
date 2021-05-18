<?php

declare (strict_types=1);
namespace RectorPrefix20210518;

return [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Comment('############################', 1), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Comment('# COMMENT', 2), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Comment('############################', 3), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo', 'foo'), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar("    Hallo\n    Welt"), 4)];
