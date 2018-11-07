<?php declare(strict_types=1);

final class MyClass
{
    public function foreachToInArray() : bool
    {
        return in_array('something', $items);
    }

    public function foreachToInArrayYoda() : bool
    {
        return in_array('something', $items);
    }

    public function foreachToInArrayStrict() : bool
    {
        return in_array('something', $items, true);
    }

    public function invertedForeachToInArrayStrict() : bool
    {
        return !in_array('something', $items, true);
    }

    public function foreachToInArrayWithToVariables() : bool
    {
        return in_array($something, $items, true);
    }

    public function foreachWithoutReturnFalse()
    {
        foreach ($items as $item) {
            if ($item === 'something') {
                return true;
            }
        }
    }

    public function foreachReturnString()
    {
        foreach ($items as $item) {
            if ($item === 'something') {
                return true;
            }
        }

        return 'false';
    }

    public function foreachWithSomethingElseAfterIt()
    {
        foreach ($items as $item) {
            if ($item === 'something') {
                return true;
            }
        }

        $foo = 'bar';
    }

    public function foreachWithElseNullable()
    {
        foreach ($items as $item) {
            if ('string') {
                return true;
            }
        }

        return;
    }

    public function alwaysTrue() : bool
    {
        foreach ($items as $item) {
            if ($something === $item) {
                return true;
            }
        }

        return true;
    }
}
