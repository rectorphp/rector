# Better Reflection

This package add 3 features:
 
- ignores missing functions without crashing
- `SmartClassReflector` always knows about file it's parsing thanks to `CurrentFileProvider` 
- add stub file to `/stubs` directory


## Stub Files

Sometimes **we need reflection of class that is not here**.

To overcome this hard dependency on `/vendor`, we can prepare a stub file in format:

```
/stubs/Namespace.ClassName.stub
```
 
When class `Namespace\ClassName` and is nowhere to be found, the stub file will be used to identify it.

### Note - `composer require --dev` related to config version
 

Sometimes it's more useful to install package of certain version, rather than to depend on version locked stubs, e.g.:
 
```
composer require --dev nette/application:2.4

```


### How to add Stub File?

- Copy original file from Github and use `y` to get absolute link to original file (in case of debug or anything).
- Also refer Rector config with version this stub was used for.
- **Add these 2 informations to the top of stub file**:

```php
<?php

# source: https://github.com/nette/application/blob/12ce71ebb7389d2c24fa6f1a57a4348cad228c5e/src/Application/UI/ITemplate.php
# for: nette24.yml

namespace Nette\...
```
