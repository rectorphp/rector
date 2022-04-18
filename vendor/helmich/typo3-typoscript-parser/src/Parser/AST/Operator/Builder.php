<?php

declare (strict_types=1);
namespace RectorPrefix20220418\Helmich\TypoScriptParser\Parser\AST\Operator;

/**
 * Helper class for quickly building operator AST nodes
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Parser\AST\Operator
 *
 * @method ObjectCreation objectCreation($path, $value, $line)
 * @method Assignment assignment($path, $value, $line)
 * @method Copy copy($path, $value, $line)
 * @method Reference reference($path, $value, $line)
 * @method Delete delete($path, $line)
 * @method ModificationCall modificationCall($method, $arguments)
 * @method Modification modification($path, $call, $line)
 */
class Builder
{
    public function __call(string $name, array $args)
    {
        $class = __NAMESPACE__ . '\\' . \ucfirst($name);
        return new $class(...$args);
    }
}
