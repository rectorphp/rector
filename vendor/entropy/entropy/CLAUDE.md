# Entropy

A tiny PHP 8.3+ framework: a reflection-based DI container (`src/Container`) plus a console runner (`src/Console`) that turns `CommandInterface::run()` signatures into CLI arguments and options.

## Layout

- `src/Container` — autowiring container, autodiscovery, contract lookup.
- `src/Console` — command registry, application bootstrap, input/output, docblock-driven argument/option mapping.
- `src/Reflection` — reflection helpers (parameter parsing, docblock `@option` marker resolver).
- `src/FileSystem`, `src/Utils`, `src/Attributes` — supporting pieces.
- `tests/` — mirrors `src/` one-to-one; fixtures live under `Fixture/` subfolders.

## Commands

```bash
vendor/bin/phpunit                  # tests
vendor/bin/ecs                      # coding standard check
vendor/bin/ecs --fix                # auto-fix
vendor/bin/rector                   # apply Rector
vendor/bin/rector --dry-run         # preview Rector changes
vendor/bin/phpstan                          # static analysis
vendor/bin/composer-dependency-analyser     # unused/shadow dependencies (config: composer-dependency-analyser.php)
```

## Conventions

- A command's `run()` signature *is* its CLI contract. First `string`/`array` parameter is a positional argument; others become `--options`. Override with `@option $name` in the docblock to force an option.
- Plural array option names are auto-singularised (`$names` → `--name`).
- Tests use PHPUnit 12; fixture classes under `tests/**/Fixture/` are excluded from Rector.
- No YAML, no attributes for wiring, no magic strings — just classes the container discovers.
