<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachToInArrayRector\Fixture;

final class MyClass
{
    public function foreachToInArray($items): bool
    {
        foreach ($items as $item) {
            if ($item == 'something') {
                return true;
            }
        }

        return false;
    }

    public function foreachToInArrayYoda($items): bool
    {
        foreach ($items as $item) {
            if ('something' == $item) {
                return true;
            }
        }

        return false;
    }

    public function foreachToInArrayStrict($items): bool
    {
        foreach ($items as $item) {
            if ($item === 'something') {
                return true;
            }
        }

        return false;
    }

    public function invertedForeachToInArrayStrict($items): bool
    {
        foreach ($items as $item) {
            if ($item === 'something') {
                return false;
            }
        }

        return true;
    }

    public function foreachToInArrayWithToVariables($items): bool
    {
        foreach ($items as $item) {
            if ($something === $item) {
                return true;
            }
        }

        return false;
    }

    public function foreachWithoutReturnFalse($items)
    {
        foreach ($items as $item) {
            if ($item === 'something') {
                return true;
            }
        }
    }

    public function foreachReturnString($items)
    {
        foreach ($items as $item) {
            if ($item === 'something') {
                return true;
            }
        }

        return 'false';
    }

    public function foreachWithSomethingElseAfterIt($items)
    {
        foreach ($items as $item) {
            if ($item === 'something') {
                return true;
            }
        }

        $foo = 'bar';
    }

    public function foreachWithElseNullable($items)
    {
        foreach ($items as $item) {
            if ('string') {
                return true;
            }
        }

        return;
    }

    public function alwaysTrue($items): bool
    {
        foreach ($items as $item) {
            if ($something === $item) {
                return true;
            }
        }

        return true;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachToInArrayRector\Fixture;

final class MyClass
{
    public function foreachToInArray($items): bool
    {
        return in_array('something', $items);
    }

    public function foreachToInArrayYoda($items): bool
    {
        return in_array('something', $items);
    }

    public function foreachToInArrayStrict($items): bool
    {
        return in_array('something', $items, true);
    }

    public function invertedForeachToInArrayStrict($items): bool
    {
        return !in_array('something', $items, true);
    }

    public function foreachToInArrayWithToVariables($items): bool
    {
        return in_array($something, $items, true);
    }

    public function foreachWithoutReturnFalse($items)
    {
        foreach ($items as $item) {
            if ($item === 'something') {
                return true;
            }
        }
    }

    public function foreachReturnString($items)
    {
        foreach ($items as $item) {
            if ($item === 'something') {
                return true;
            }
        }

        return 'false';
    }

    public function foreachWithSomethingElseAfterIt($items)
    {
        foreach ($items as $item) {
            if ($item === 'something') {
                return true;
            }
        }

        $foo = 'bar';
    }

    public function foreachWithElseNullable($items)
    {
        foreach ($items as $item) {
            if ('string') {
                return true;
            }
        }

        return;
    }

    public function alwaysTrue($items): bool
    {
        foreach ($items as $item) {
            if ($something === $item) {
                return true;
            }
        }

        return true;
    }
}

?>
