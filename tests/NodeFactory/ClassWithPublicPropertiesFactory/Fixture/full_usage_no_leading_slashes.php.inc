{
    "fullyQualifiedName": "MyNamespace\\MyClassName",
    "properties": {
        "foo": {
            "type": "string"
        },
        "bar": {
            "type": "int",
            "nullable": true
        }
    },
    "parent": "AnotherNamespace\\ParentClass",
    "traits": [
        "FooNamespace\\FirstTrait",
        "BarNamespace\\SecondTrait",
        "NoNamespaceThirdTrait"
    ]
}
-----
<?php

namespace MyNamespace;

class MyClassName extends \AnotherNamespace\ParentClass
{
    use \FooNamespace\FirstTrait;
    use \BarNamespace\SecondTrait;
    use \NoNamespaceThirdTrait;
    public string $foo;
    public ?int $bar;
}
