<?php

declare(strict_types=1);

namespace Rector\Nette\Contract\ValueObject;

use PhpParser\Node\Expr;

interface ParameterArrayInterface
{
    /**
     * @return array<string, Expr>
     */
    public function getTemplateVariables(): array;

    /**
     * @return string[]
     */
    public function getConditionalVariableNames(): array;
}
