<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

use Rector\Core\Exception\ShouldNotHappenException;

final class ParamArrayOffset
{
    /**
     * @param array<string, mixed> $data
     */
    public function runFirst($data)
    {
        if (! isset($data['key'])) {
            throw new ShouldNotHappenException();
        }

        $this->process($data);
    }

    private function process($data)
    {
        echo $data['key'];
    }
}
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

use Rector\Core\Exception\ShouldNotHappenException;

final class ParamArrayOffset
{
    /**
     * @param array<string, mixed> $data
     */
    public function runFirst($data)
    {
        if (! isset($data['key'])) {
            throw new ShouldNotHappenException();
        }

        $this->process($data);
    }

    private function process(array $data)
    {
        echo $data['key'];
    }
}
