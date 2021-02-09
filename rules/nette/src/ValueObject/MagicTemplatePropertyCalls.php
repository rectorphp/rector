<?php

declare(strict_types=1);

namespace Rector\Nette\ValueObject;

use PhpParser\Node;
use PhpParser\Node\Expr;

final class MagicTemplatePropertyCalls
{
    /**
     * @var Node[]
     */
    private $nodesToRemove = [];

    /**
     * @var Expr[]
     */
    private $templateVariables = [];

    /**
     * @var Expr[]
     */
    private $templateFileExprs = [];

    /**
     * @param Expr[] $templateFileExprs
     * @param Expr[] $templateVariables
     * @param Node[] $nodesToRemove
     */
    public function __construct(array $templateFileExprs, array $templateVariables, array $nodesToRemove)
    {
        $this->templateFileExprs = $templateFileExprs;
        $this->templateVariables = $templateVariables;
        $this->nodesToRemove = $nodesToRemove;
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
     * @return Expr[]
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
     * @return Expr[]
     */
    private function getTemplateFileExprs(): array
    {
        return $this->templateFileExprs;
    }
}
