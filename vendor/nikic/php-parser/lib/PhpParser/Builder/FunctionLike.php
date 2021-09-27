<?php

declare (strict_types=1);
namespace PhpParser\Builder;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
abstract class FunctionLike extends \PhpParser\Builder\Declaration
{
    protected $returnByRef = \false;
    protected $params = [];
    /** @var string|Node\Name|Node\NullableType|null */
    protected $returnType = null;
    /**
     * Make the function return by reference.
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function makeReturnByRef()
    {
        $this->returnByRef = \true;
        return $this;
    }
    /**
     * Adds a parameter.
     *
     * @param Node\Param|Param $param The parameter to add
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function addParam($param)
    {
        $param = \PhpParser\BuilderHelpers::normalizeNode($param);
        if (!$param instanceof \PhpParser\Node\Param) {
            throw new \LogicException(\sprintf('Expected parameter node, got "%s"', $param->getType()));
        }
        $this->params[] = $param;
        return $this;
    }
    /**
     * Adds multiple parameters.
     *
     * @param array $params The parameters to add
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function addParams($params)
    {
        foreach ($params as $param) {
            $this->addParam($param);
        }
        return $this;
    }
    /**
     * Sets the return type for PHP 7.
     *
     * @param string|Node\Name|Node\Identifier|Node\ComplexType $type
     *
     * @return $this The builder instance (for fluid interface)
     */
    public function setReturnType($type)
    {
        $this->returnType = \PhpParser\BuilderHelpers::normalizeType($type);
        return $this;
    }
}
