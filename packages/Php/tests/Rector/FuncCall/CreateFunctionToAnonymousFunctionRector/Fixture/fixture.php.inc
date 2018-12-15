<?php

class ClassWithCreateFunction
{
    public function run()
    {
        $callback = create_function('$matches', "return strtolower(\$matches[1]);");

        $delimiter = '___';
        $callback = create_function('$matches', "return '$delimiter' . strtolower(\$matches[1]);");
    }
}

?>
-----
<?php

class ClassWithCreateFunction
{
    public function run()
    {
        $callback = function ($matches) {
            return strtolower($matches[1]);
        };

        $delimiter = '___';
        $callback = function ($matches) use($delimiter) {
            return $delimiter . strtolower($matches[1]);
        };
    }
}

?>
