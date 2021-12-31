<?php

declare (strict_types=1);
namespace RectorPrefix20211231\Helmich\TypoScriptParser\Parser\AST\Operator;

use RectorPrefix20211231\Helmich\TypoScriptParser\Parser\AST\ObjectPath;
use Helmich\TypoScriptParser\Parser\AST\Statement;
/**
 * Abstract base class for statements with binary operators.
 *
 * @package    Helmich\TypoScriptParser
 * @subpcakage Parser\AST\Operator
 */
abstract class BinaryOperator extends \Helmich\TypoScriptParser\Parser\AST\Statement
{
    /**
     * The object on the left-hand side of the statement.
     *
     * @var ObjectPath
     */
    public $object;
}
