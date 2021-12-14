<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionPropertyGetDefaultValueRector\Fixture;

use PHPStan\Reflection\Php\PhpPropertyReflection;

class PullFromPHPStanReflection
{
    public function get(PhpPropertyReflection $phpPropertyReflection)
    {
        $reflectionProperty = $phpPropertyReflection->getNativeReflection();
        return is_array($reflectionProperty->getDefaultValue());
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\MethodCall\DowngradeReflectionPropertyGetDefaultValueRector\Fixture;

use PHPStan\Reflection\Php\PhpPropertyReflection;

class PullFromPHPStanReflection
{
    public function get(PhpPropertyReflection $phpPropertyReflection)
    {
        $reflectionProperty = $phpPropertyReflection->getNativeReflection();
        return is_array($reflectionProperty->getDeclaringClass()->getDefaultProperties()[$reflectionProperty->getName()] ?? null);
    }
}

?>
