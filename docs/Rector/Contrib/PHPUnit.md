## PHPUnit Rectors

All methods are changes by default. But **you can specify methods** you like:

````yaml
rectors:
    Rector\Rector\Contrib\PHPUnit\SpecificMethod\AssertTrueFalseToSpecificMethodRector:
        - 'is_file'
```
