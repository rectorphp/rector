<?php

declare (strict_types=1);
namespace RectorPrefix20210518;

use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\NestedAssignment;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\ObjectCreation;
use RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar;
return [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\NestedAssignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('page', 'page'), [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\NestedAssignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('page.meta', 'meta'), [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\ObjectCreation(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('page.meta.foo:bar.cObject', 'foo:bar.cObject'), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar("TEXT"), 3), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('page.meta.foo:bar.cObject.value', 'foo:bar.cObject.value'), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar("qux"), 4)], 2)], 1)];
