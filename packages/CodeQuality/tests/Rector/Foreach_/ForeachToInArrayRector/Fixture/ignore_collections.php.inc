<?php

namespace Rector\CodeQuality\Tests\Rector\Foreach_\ForeachToInArrayRector\Fixture;

use Rector\CodeQuality\Tests\Rector\Foreach_\ForeachToInArrayRector\Source\Collection;

final class IgnoreCollections
{
    public function foreachToInArray(): bool
    {
        $items = new Collection();
        foreach ($items as $item) {
            if ($item == 'something') {
                return true;
            }
        }

        return false;
    }

    public function foreachToInArrayYoda(): bool
    {
        $items = new Collection();
        foreach ($items as $item) {
            if ('something' == $item) {
                return true;
            }
        }

        return false;
    }

    public function foreachToInArrayStrict(): bool
    {
        $items = new Collection();
        foreach ($items as $item) {
            if ($item === 'something') {
                return true;
            }
        }

        return false;
    }

    public function invertedForeachToInArrayStrict(): bool
    {
        $items = new Collection();
        foreach ($items as $item) {
            if ($item === 'something') {
                return false;
            }
        }

        return true;
    }

    public function foreachToInArrayWithToVariables(): bool
    {
        $items = new Collection();
        foreach ($items as $item) {
            if ($something === $item) {
                return true;
            }
        }

        return false;
    }

    public function foreachWithoutReturnFalse()
    {
        $items = new Collection();
        foreach ($items as $item) {
            if ($item === 'something') {
                return true;
            }
        }
    }

    public function foreachReturnString()
    {
        $items = new Collection();
        foreach ($items as $item) {
            if ($item === 'something') {
                return true;
            }
        }

        return 'false';
    }

    public function foreachWithSomethingElseAfterIt()
    {
        $items = new Collection();
        foreach ($items as $item) {
            if ($item === 'something') {
                return true;
            }
        }

        $foo = 'bar';
    }

    public function foreachWithElseNullable()
    {
        $items = new Collection();
        foreach ($items as $item) {
            if ('string') {
                return true;
            }
        }

        return;
    }

    public function foreachWithElseFalsable()
    {
        $items = new Collection();
        foreach ($items as $item) {
            if ('string') {
                return true;
            }
        }

        return false;
    }

    public function alwaysTrue(): bool
    {
        $items = new Collection();
        foreach ($items as $item) {
            if ($something === $item) {
                return true;
            }
        }

        return true;
    }
}

?>
