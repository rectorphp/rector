<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

final class TypeCompatibleDefault
{
    public function runFirst()
    {
        $this->process('first');
    }

    public function runEmpty()
    {
        $this->process();
    }

    private function process($data = 'yes')
    {
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector\Fixture;

final class TypeCompatibleDefault
{
    public function runFirst()
    {
        $this->process('first');
    }

    public function runEmpty()
    {
        $this->process();
    }

    private function process(string $data = 'yes')
    {
    }
}

?>
