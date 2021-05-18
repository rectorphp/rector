<?php

declare (strict_types=1);
namespace RectorPrefix20210518;

use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\NopStatement;
return [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Modification(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo', 'foo'), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\ModificationCall('appendString', 'some string with () braces in it'), 1), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\NopStatement(2)];
