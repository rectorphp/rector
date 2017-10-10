# Deprecation Extractor

This package extracts 2 cases:
 
- `@deprecate` annotation
- `trigger_error(*, E_USER_DEPRECATED)` from the code.

It helps to generated automate rectors and identify BC changes purely from the code.
 

## How it works?

1. Just read the message or node
2. Guess what rector might change the code
3. Create `RectorGuess` class that holds rector suggetion

    - rector class
    - certainity (from 0 to 100)
    - related node  
    - related message (only for `@deprecate`)

4. Show it in console table


## How it helps you?

Instead of using your eyes and brain to read `CHANGELOG.md` or `UPGRADE-x.md`, just run it over directory of framework you use:

```bash
vendor/bin/rector extract-deprecations vendor/nette/application/src
```

It will show you what changed and how. 
