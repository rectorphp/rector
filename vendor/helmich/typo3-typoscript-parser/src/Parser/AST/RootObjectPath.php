<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Helmich\TypoScriptParser\Parser\AST;

/**
 * Class RootObjectPath
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Parser\AST
 */
class RootObjectPath extends ObjectPath
{
    /**
     * RootObjectPath constructor.
     */
    public function __construct()
    {
        parent::__construct('', '');
    }
    /**
     * @return ObjectPath
     */
    public function parent() : ObjectPath
    {
        return $this;
    }
    /**
     * @return int
     */
    public function depth() : int
    {
        return 0;
    }
    /**
     * @param string $name
     * @return ObjectPath
     */
    public function append($name) : ObjectPath
    {
        return new ObjectPath(\ltrim($name, '.'), $name);
    }
}
