<?php

namespace Rector\Core\Tests\Rector\ClassMethod\GetRectorsWithConfigurationToProvideConfigFileInfoRector\Fixture;

final class SomeClass
{
    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            NewArgToMethodCallRector::class => [
                NewArgToMethodCallRector::NEW_ARGS_TO_METHOD_CALLS => [
                    new NewArgToMethodCall(SomeDotenv::class, true, 'usePutenv'),
                ],
            ],
        ];
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Rector\ClassMethod\GetRectorsWithConfigurationToProvideConfigFileInfoRector\Fixture;

final class SomeClass
{
    protected function provideConfigFileInfo(): ?\Symplify\SmartFileSystem\SmartFileInfo
    {
        return new \Symplify\SmartFileSystem\SmartFileInfo(__DIR__ . '/config/configured_rule.php');
    }
}

?>
