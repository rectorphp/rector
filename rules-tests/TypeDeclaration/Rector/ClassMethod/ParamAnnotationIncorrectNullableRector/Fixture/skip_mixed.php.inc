<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class SkipMixed
{
    /**
     * @param mixed $default
     */
    public function getEnvVar(string $var, mixed $default = null): mixed
    {
        return $this->env[$var] ?? $default;
    }
}
