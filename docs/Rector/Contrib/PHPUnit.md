## PHPUnit Rectors

All methods are changes by default. But **you can specify methods** you like:

````yaml
services:
    Rector\PHPUnit\Rector\SpecificMethod\AssertTrueFalseToSpecificMethodRector:
        $activeMethods:
            - 'is_file'
```
