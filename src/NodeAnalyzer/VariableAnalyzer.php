<?php

declare (strict_types=1);
namespace Rector\NodeAnalyzer;

use PhpParser\Node\Expr\Variable;
use Rector\NodeTypeResolver\Node\AttributeKey;
final class VariableAnalyzer
{
    public function isStaticOrGlobal(Variable $variable) : bool
    {
        if ($variable->getAttribute(AttributeKey::IS_GLOBAL_VAR) === \true) {
            return \true;
        }
        return $variable->getAttribute(AttributeKey::IS_STATIC_VAR) === \true;
    }
    public function isUsedByReference(Variable $variable) : bool
    {
        return $variable->getAttribute(AttributeKey::IS_BYREF_VAR) === \true;
    }
}
