<?php

declare (strict_types=1);
namespace Rector\Symfony\Configs\ValueObject;

use PhpParser\Node\Expr;
final class ServiceArguments
{
    /**
     * @readonly
     * @var string
     */
    private $className;
    /**
     * @var array<(string | int), (string | Expr)>
     * @readonly
     */
    private $params;
    /**
     * @var array<(string | int), (string | Expr)>
     * @readonly
     */
    private $envs;
    /**
     * @param array<string|int, string|Expr> $params
     * @param array<string|int, string|Expr> $envs
     */
    public function __construct(string $className, array $params, array $envs)
    {
        $this->className = $className;
        $this->params = $params;
        $this->envs = $envs;
    }
    public function getClassName() : string
    {
        return $this->className;
    }
    /**
     * @return array<string|int, string|Expr>
     */
    public function getParams() : array
    {
        return $this->params;
    }
    /**
     * @return array<string|int, string|Expr>
     */
    public function getEnvs() : array
    {
        return $this->envs;
    }
}
