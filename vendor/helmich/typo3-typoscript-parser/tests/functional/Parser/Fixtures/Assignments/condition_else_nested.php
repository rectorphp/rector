<?php

declare (strict_types=1);
namespace RectorPrefix20210518;

return [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ConditionalStatement('[globalVar = GP:foo=1]', [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\NestedAssignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo', 'foo'), [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo.bar', 'bar'), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar('1'), 3)], 2), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\MultilineComment('/*
Hello
World
*/', 5)], [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\MultilineComment('/*
Hello
World
*/', 10), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\NestedAssignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo', 'foo'), [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo.bar', 'bar'), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar('2'), 15)], 14)], 1)];
