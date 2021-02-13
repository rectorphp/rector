<?php

declare(strict_types=1);

namespace Rector\Nette\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;

final class MagicTemplatePropertyCalls
{
    /**
     * @var Node[]
     */
    private $nodesToRemove = [];

    /**
     * @var array<string, Expr>
     */
    private $templateVariables = [];

    /**
     * @var Expr[]
     */
    private $templateFileExprs = [];

    /**
     * @var array<string, Assign[]>
     */
    private $conditionalAssigns = [];

    /**
     * @param Expr[] $templateFileExprs
     * @param array<string, Expr> $templateVariables
     * @param Node[] $nodesToRemove
     * @param array<string, Assign[]> $conditionalAssigns
     */
    public function __construct(
        array $templateFileExprs,
        array $templateVariables,
        array $nodesToRemove,
        array $conditionalAssigns
    ) {
        $this->templateFileExprs = $templateFileExprs;
        $this->templateVariables = $templateVariables;
        $this->nodesToRemove = $nodesToRemove;
        $this->conditionalAssigns = $conditionalAssigns;
    }

    public function getFirstTemplateFileExpr(): ?Expr
    {
        return $this->templateFileExprs[0] ?? null;
    }

    public function hasMultipleTemplateFileExprs(): bool
    {
        return count($this->templateFileExprs) > 1;
    }

    /**
     * @return array<string, Expr>
     */
    public function getTemplateVariables(): array
    {
        return $this->templateVariables;
    }

    /**
     * @return Node[]
     */
    public function getNodesToRemove(): array
    {
        return $this->nodesToRemove;
    }

    /**
     * @return array<string, Assign[]>
     */
    public function getConditionalAssigns(): array
    {
        return $this->conditionalAssigns;
    }

    /**
     * @return string[]
     */
    public function getConditionalVariableNames(): array
    {
        return array_keys($this->conditionalAssigns);
    }
}
