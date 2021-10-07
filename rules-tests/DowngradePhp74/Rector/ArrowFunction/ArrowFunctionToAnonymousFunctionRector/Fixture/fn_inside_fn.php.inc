<?php

namespace Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

final class DemoFile
{
    public function run()
    {
        $inputObjectList = [/* ... */];
        $serviceObjectList = [/* ... */];
        $containsAllServiceObjectList = array_filter(
            $inputObjectList,
            fn ($object) => array_udiff(
                $serviceObjectList,
                $object->getServiceObjects(),
                fn ($object1, $object2) => get_class($object1) <=> get_class($object2),
            ) === [],
        );
    }
}
?>
-----
<?php

namespace Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\Fixture;

final class DemoFile
{
    public function run()
    {
        $inputObjectList = [/* ... */];
        $serviceObjectList = [/* ... */];
        $containsAllServiceObjectList = array_filter(
            $inputObjectList,
            function ($object) use ($serviceObjectList) {
                return array_udiff(
                    $serviceObjectList,
                    $object->getServiceObjects(),
                    function ($object1, $object2) {
                        return get_class($object1) <=> get_class($object2);
                    },
                ) === [];
            },
        );
    }
}
?>
