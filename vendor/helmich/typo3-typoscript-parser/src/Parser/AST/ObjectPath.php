<?php

declare (strict_types=1);
namespace RectorPrefix20211231\Helmich\TypoScriptParser\Parser\AST;

/**
 * An object path.
 *
 * @package    Helmich\TypoScriptParser
 * @subpackage Parser\AST
 */
class ObjectPath
{
    /**
     * The relative object path, as specified in the source code.
     *
     * @var string
     */
    public $relativeName;
    /**
     * The absolute object path, as evaluated from parent nested statements.
     *
     * @var string
     */
    public $absoluteName;
    /**
     * Constructs a new object path.
     *
     * @param string $absoluteName The absolute object path.
     * @param string $relativeName The relative object path.
     */
    public function __construct(string $absoluteName, string $relativeName)
    {
        $this->absoluteName = $absoluteName;
        $this->relativeName = $relativeName;
    }
    /**
     * @return int
     */
    public function depth() : int
    {
        return \count(\explode('.', $this->absoluteName));
    }
    /**
     * Builds the path to the parent object.
     *
     * @return ObjectPath The path to the parent object.
     */
    public function parent() : \RectorPrefix20211231\Helmich\TypoScriptParser\Parser\AST\ObjectPath
    {
        $components = \explode('.', $this->absoluteName);
        if (\count($components) === 1) {
            return new \RectorPrefix20211231\Helmich\TypoScriptParser\Parser\AST\RootObjectPath();
        }
        \array_pop($components);
        return new self(\implode('.', $components), $components[\count($components) - 1]);
    }
    /**
     * @param string $name
     * @return self
     */
    public function append(string $name) : self
    {
        if ($name[0] === '.') {
            return new self($this->absoluteName . $name, $name);
        }
        return new self($this->absoluteName . '.' . $name, $name);
    }
}
