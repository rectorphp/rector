<?php

declare (strict_types=1);
namespace Rector\Arguments\NodeAnalyzer;

use PhpParser\Node\Param;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\NodeNameResolver\NodeNameResolver;
final class ChangedArgumentsDetector
{
    /**
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\Rector\Core\PhpParser\Node\Value\ValueResolver $valueResolver, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->valueResolver = $valueResolver;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param mixed $value
     */
    public function isDefaultValueChanged(\PhpParser\Node\Param $param, $value) : bool
    {
        if ($param->default === null) {
            return \false;
        }
        return !$this->valueResolver->isValue($param->default, $value);
    }
    public function isTypeChanged(\PhpParser\Node\Param $param, ?string $type) : bool
    {
        if ($param->type === null) {
            return \false;
        }
        if ($type === null) {
            return \true;
        }
        return !$this->nodeNameResolver->isName($param->type, $type);
    }
}
