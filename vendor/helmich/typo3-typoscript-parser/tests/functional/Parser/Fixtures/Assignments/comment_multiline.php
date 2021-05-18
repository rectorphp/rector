<?php

declare (strict_types=1);
namespace RectorPrefix20210518;

return [new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\MultilineComment('/*
Hello
World
*/', 1), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Operator\Assignment(new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\ObjectPath('foo', 'foo'), new \RectorPrefix20210518\Helmich\TypoScriptParser\Parser\AST\Scalar('bar'), 5)];
