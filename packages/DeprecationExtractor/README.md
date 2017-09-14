# Deprecation Extractor

This package extracts 2 cases:
 
- `@deprecate` annotation
- `trigger_error(*, E_USER_DEPRECATED)` from the code.

It helps to generated automate rectors and identify BC changes purely from the code.
 

## How it works?

1. Just read the message
2. Detect what code should be change into what code
3. Create `Deprecation` class that holds information about the change - e.g. `ClassMethodRepcation` 
4. Create dynamic rectors that will do the work


## How it helps you?

Instead of using your eyes and brain to read `CHANGELOG.md` or `UPGRADE-x.md`, just run it over directory of framework you use:

```bash
vendor/bin/rector extract-deprecations vendor/nette/application/src
```

It will show you what changed and how. 

Moreover, it will change the code for you instead of writing manuall rectors.
