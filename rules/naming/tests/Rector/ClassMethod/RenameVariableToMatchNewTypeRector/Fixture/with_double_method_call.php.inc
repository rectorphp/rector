<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ClassConstFetch;

final class WithDoubleMethodCall
{
    public function run()
    {
        $flags = $this->prepareFlags(true);
        dump($flags);
    }

    private function prepareFlags(bool $currentValue): BitwiseOr
    {
        $magicGet = $this->createClassConstFetch('Symfony\Component\PropertyAccess\PropertyAccessor', 'MAGIC_GET');
        $magicSet = $this->createClassConstFetch('Symfony\Component\PropertyAccess\PropertyAccessor', 'MAGIC_SET');
        if (!$currentValue) {
            return new BitwiseOr($magicGet, $magicSet);
        }

        return new BitwiseOr(
            new BitwiseOr(
                $this->createClassConstFetch('Symfony\Component\PropertyAccess\PropertyAccessor', 'MAGIC_CALL'),
                $magicGet,
            ),
            $magicSet,
        );
    }

    protected function createClassConstFetch(string $class, string $constant): ClassConstFetch
    {
        return new ClassConstFetch($class, $constant);
    }
}
?>
-----
<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameVariableToMatchNewTypeRector\Fixture;

use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\ClassConstFetch;

final class WithDoubleMethodCall
{
    public function run()
    {
        $bitwiseOr = $this->prepareFlags(true);
        dump($bitwiseOr);
    }

    private function prepareFlags(bool $currentValue): BitwiseOr
    {
        $magicGet = $this->createClassConstFetch('Symfony\Component\PropertyAccess\PropertyAccessor', 'MAGIC_GET');
        $magicSet = $this->createClassConstFetch('Symfony\Component\PropertyAccess\PropertyAccessor', 'MAGIC_SET');
        if (!$currentValue) {
            return new BitwiseOr($magicGet, $magicSet);
        }

        return new BitwiseOr(
            new BitwiseOr(
                $this->createClassConstFetch('Symfony\Component\PropertyAccess\PropertyAccessor', 'MAGIC_CALL'),
                $magicGet,
            ),
            $magicSet,
        );
    }

    protected function createClassConstFetch(string $class, string $constant): ClassConstFetch
    {
        return new ClassConstFetch($class, $constant);
    }
}
?>
