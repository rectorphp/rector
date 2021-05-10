<?php

declare (strict_types=1);
namespace Rector\Nette\ValueObject;

use PhpParser\Node\Expr;
use Rector\Nette\Contract\ValueObject\ParameterArrayInterface;
final class TemplateParametersAssigns implements \Rector\Nette\Contract\ValueObject\ParameterArrayInterface
{
    /**
     * @var AlwaysTemplateParameterAssign[]
     */
    private $templateParameterAssigns = [];
    /**
     * @var ConditionalTemplateParameterAssign[]
     */
    private $conditionalTemplateParameterAssign = [];
    /**
     * @param AlwaysTemplateParameterAssign[] $alwaysTemplateParameterAssigns
     * @param ConditionalTemplateParameterAssign[] $conditionalTemplateParameterAssigns
     */
    public function __construct(array $alwaysTemplateParameterAssigns, array $conditionalTemplateParameterAssigns)
    {
        $this->templateParameterAssigns = $alwaysTemplateParameterAssigns;
        $this->conditionalTemplateParameterAssign = $conditionalTemplateParameterAssigns;
    }
    /**
     * @return ConditionalTemplateParameterAssign[]
     */
    public function getConditionalTemplateParameterAssign() : array
    {
        return $this->conditionalTemplateParameterAssign;
    }
    /**
     * @return string[]
     */
    public function getConditionalVariableNames() : array
    {
        $conditionalVariableNames = [];
        foreach ($this->conditionalTemplateParameterAssign as $conditionalTemplateParameterAssign) {
            $conditionalVariableNames[] = $conditionalTemplateParameterAssign->getParameterName();
        }
        return \array_unique($conditionalVariableNames);
    }
    /**
     * @return AlwaysTemplateParameterAssign[]
     */
    public function getTemplateParameterAssigns() : array
    {
        return $this->templateParameterAssigns;
    }
    /**
     * @return array<string, Expr>
     */
    public function getTemplateVariables() : array
    {
        $templateVariables = [];
        foreach ($this->templateParameterAssigns as $templateParameterAssign) {
            $templateVariables[$templateParameterAssign->getParameterName()] = $templateParameterAssign->getAssignedExpr();
        }
        return $templateVariables;
    }
}
