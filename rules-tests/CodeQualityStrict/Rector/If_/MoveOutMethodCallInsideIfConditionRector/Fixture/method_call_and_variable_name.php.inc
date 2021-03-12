<?php

namespace Rector\CodeQualityStrict\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ThisType;

final class MethodCallAndVariableName
{
    public function run(Scope $scope)
    {
        $apple = new Variable('apple');
        if ($scope->getType($apple) instanceof ThisType) {
            return;
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQualityStrict\Tests\Rector\If_\MoveOutMethodCallInsideIfConditionRector\Fixture;

use PhpParser\Node\Expr\Variable;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ThisType;

final class MethodCallAndVariableName
{
    public function run(Scope $scope)
    {
        $apple = new Variable('apple');
        $appleType = $scope->getType($apple);
        if ($appleType instanceof ThisType) {
            return;
        }
    }
}

?>
