<?php

declare (strict_types=1);
namespace RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\Operator;

use RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\ObjectPath;
/**
 * A reference statement.
 *
 * Example:
 *
 *     foo = bar
 *     baz <= foo
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Parser\AST\Operator
 */
class Reference extends \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\Operator\BinaryObjectOperator
{
    /**
     * Constructs a new reference statement.
     *
     * @param ObjectPath $object     The reference object.
     * @param ObjectPath $target     The target object.
     * @param int        $sourceLine The original source line.
     */
    public function __construct(\RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\ObjectPath $object, \RectorPrefix20211020\Helmich\TypoScriptParser\Parser\AST\ObjectPath $target, int $sourceLine)
    {
        parent::__construct($sourceLine);
        $this->object = $object;
        $this->target = $target;
    }
}
