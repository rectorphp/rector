<?php

declare (strict_types=1);
namespace Rector\Nette\NodeAnalyzer;

use PhpParser\Node\Expr\Variable;
use Rector\Nette\ValueObject\TemplateParametersAssigns;
/**
 * Replaces:
 *
 * if (...) { $this->template->key = 'some'; } else { $this->template->key = 'another'; }
 *
 * ↓
 *
 * if (...) { $key = 'some'; } else { $key = 'another'; }
 */
final class ConditionalTemplateAssignReplacer
{
    public function processClassMethod(\Rector\Nette\ValueObject\TemplateParametersAssigns $templateParametersAssigns) : void
    {
        foreach ($templateParametersAssigns->getNonSingleParameterAssigns() as $parameterAssign) {
            $assign = $parameterAssign->getAssign();
            $assign->var = new \PhpParser\Node\Expr\Variable($parameterAssign->getParameterName());
        }
    }
}
