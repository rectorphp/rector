<?php

declare (strict_types=1);
namespace RectorPrefix20220501\Helmich\TypoScriptParser\Parser\AST\Operator;

use RectorPrefix20220501\Helmich\TypoScriptParser\Parser\AST\ObjectPath;
/**
 * Abstract base class for statements with binary operators.
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Parser\AST\Operator
 */
abstract class BinaryObjectOperator extends \RectorPrefix20220501\Helmich\TypoScriptParser\Parser\AST\Operator\BinaryOperator
{
    /**
     * The target object to reference to or copy from.
     *
     * @var ObjectPath
     */
    public $target;
}
