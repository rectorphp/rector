<?php

namespace Rector\Core\Tests\Issues\RemoveReadonlyLocalVariable\Fixture;

final class RemoveVariableAssignOnly
{
    public function run()
    {
        $replacements = [
            'PHPUnit\Framework\TestCase\Notice' => 'expectNotice',
            'PHPUnit\Framework\TestCase\Deprecated' => 'expectDeprecation',
        ];
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\RemoveReadonlyLocalVariable\Fixture;

final class RemoveVariableAssignOnly
{
    public function run()
    {
    }
}

?>
