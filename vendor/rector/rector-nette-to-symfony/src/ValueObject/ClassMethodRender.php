<?php

declare (strict_types=1);
namespace Rector\NetteToSymfony\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use Rector\Nette\Contract\ValueObject\ParameterArrayInterface;
final class ClassMethodRender implements \Rector\Nette\Contract\ValueObject\ParameterArrayInterface
{
    /**
     * @var mixed[]
     */
    private $templateFileExprs;
    /**
     * @var mixed[]
     */
    private $templateVariables;
    /**
     * @var mixed[]
     */
    private $nodesToRemove;
    /**
     * @var mixed[]
     */
    private $conditionalAssigns;
    /**
     * @param Expr[] $templateFileExprs
     * @param array<string, Expr> $templateVariables
     * @param Node[] $nodesToRemove
     * @param array<string, Assign[]> $conditionalAssigns
     */
    public function __construct(array $templateFileExprs, array $templateVariables, array $nodesToRemove, array $conditionalAssigns)
    {
        $this->templateFileExprs = $templateFileExprs;
        $this->templateVariables = $templateVariables;
        $this->nodesToRemove = $nodesToRemove;
        $this->conditionalAssigns = $conditionalAssigns;
    }
    /**
     * @return array<string, Expr>
     */
    public function getTemplateVariables() : array
    {
        return $this->templateVariables;
    }
    /**
     * @return string[]
     */
    public function getConditionalVariableNames() : array
    {
        return \array_keys($this->conditionalAssigns);
    }
    /**
     * @return Node[]
     */
    public function getNodesToRemove() : array
    {
        return $this->nodesToRemove;
    }
    public function getFirstTemplateFileExpr() : ?\PhpParser\Node\Expr
    {
        return $this->templateFileExprs[0] ?? null;
    }
}
