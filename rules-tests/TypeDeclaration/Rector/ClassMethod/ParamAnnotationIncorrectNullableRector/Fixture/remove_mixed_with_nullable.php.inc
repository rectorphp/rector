<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class RemoveMixedWithNullable
{
    /**
     * @param mixed|null $default
     */
    public function getEnvVar(string $var, mixed $default = null): mixed
    {
        return $this->env[$var] ?? $default;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ParamAnnotationIncorrectNullableRector\Fixture;

final class RemoveMixedWithNullable
{
    /**
     * @param mixed $default
     */
    public function getEnvVar(string $var, mixed $default = null): mixed
    {
        return $this->env[$var] ?? $default;
    }
}

?>
