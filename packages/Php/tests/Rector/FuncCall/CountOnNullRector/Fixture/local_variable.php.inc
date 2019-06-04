<?php

namespace Rector\Php\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

use Rector\Php\Tests\Rector\FuncCall\CountOnNullRector\Source\CountableClass;

final class LocalVariable
{
    public function methodWithLocalVariableInitializedAsArray(array $anArray): ?array
    {
        $results = [];

        foreach ($anArray as $value) {
            $results[] = $value;
        }

        return count($results) > 0 ? $results : null;
    }

    public function methodWithLocalVariableArrayAsResult(string $string, array $regexes): ?array
    {
        $results = [];

        foreach ($regexes as $toFind) {
            if (false !== preg_match_all($toFind, $string, $result)) {
                $results[] = $result[0];
            }
        }

        // Flatten the array
        $results = array_merge(...$results);

        return count($results) > 0 ? $results : null;
    }

    public function methodWithLocalVariableInitializedAsArrayAndTransformedIntoString(array $anArray): ?array
    {
        $results = [];

        // This code may not have much sense,
        // but in real world scenario it may
        // appear very similar to this.
        foreach ($anArray as $value) {
            if ('condition' === $value) {
                $results = 'transform into a string';
            } elseif (is_array($results)) {
                $results[] = $value;
            }
        }

        return count($results) > 0 ? $results : null;
    }

    public function methodWithLocalVariableInitializedAsArrayAndTransformedIntoCountable(array $anArray): ?array
    {
        $results = [];

        // This code may not have much sense,
        // but in real world scenario it may
        // appear very similar to this.
        foreach ($anArray as $value) {
            if ('condition' === $value) {
                $results = new CountableClass();
            }
        }

        return count($results) > 0 ? $results : null;
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FuncCall\CountOnNullRector\Fixture;

use Rector\Php\Tests\Rector\FuncCall\CountOnNullRector\Source\CountableClass;

final class LocalVariable
{
    public function methodWithLocalVariableInitializedAsArray(array $anArray): ?array
    {
        $results = [];

        foreach ($anArray as $value) {
            $results[] = $value;
        }

        return count($results) > 0 ? $results : null;
    }

    public function methodWithLocalVariableArrayAsResult(string $string, array $regexes): ?array
    {
        $results = [];

        foreach ($regexes as $toFind) {
            if (false !== preg_match_all($toFind, $string, $result)) {
                $results[] = $result[0];
            }
        }

        // Flatten the array
        $results = array_merge(...$results);

        return count($results) > 0 ? $results : null;
    }

    public function methodWithLocalVariableInitializedAsArrayAndTransformedIntoString(array $anArray): ?array
    {
        $results = [];

        // This code may not have much sense,
        // but in real world scenario it may
        // appear very similar to this.
        foreach ($anArray as $value) {
            if ('condition' === $value) {
                $results = 'transform into a string';
            } elseif (is_array($results)) {
                $results[] = $value;
            }
        }

        return (is_array($results) || $results instanceof \Countable ? count($results) : 0) > 0 ? $results : null;
    }

    public function methodWithLocalVariableInitializedAsArrayAndTransformedIntoCountable(array $anArray): ?array
    {
        $results = [];

        // This code may not have much sense,
        // but in real world scenario it may
        // appear very similar to this.
        foreach ($anArray as $value) {
            if ('condition' === $value) {
                $results = new CountableClass();
            }
        }

        return (is_array($results) || $results instanceof \Countable ? count($results) : 0) > 0 ? $results : null;
    }
}

?>
