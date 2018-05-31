# Better Reflection

This package add 3 features:

- ignores missing functions without crashing
- `SmartClassReflector` knows about every file passed as source

### Note - `composer require --dev` related to config version

Sometimes it's more useful to install package of certain version, rather than to depend on version locked stubs, e.g.:

```
composer require --dev nette/application:2.4
```
