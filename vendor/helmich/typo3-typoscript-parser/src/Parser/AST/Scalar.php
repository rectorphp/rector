<?php

declare (strict_types=1);
namespace RectorPrefix20211231\Helmich\TypoScriptParser\Parser\AST;

/**
 * A scalar value.
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Parser\AST
 */
class Scalar
{
    /**
     * The value.
     *
     * @var string
     */
    public $value;
    /**
     * Constructs a scalar value.
     *
     * @param string $value The value.
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }
}
