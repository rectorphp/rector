# How Run One Rule From Command Line

Do you have config that includes many sets and Rectors? You might want to run only a single Rector. The `--only` argument allows that, e.g.:

```bash
vendor/bin/rector process src --set solid --only Rector\SOLID\Rector\Class_\FinalizeClassesWithoutChildrenRector
```

Too long? Use a short class name:

```bash
vendor/bin/rector process src --set solid --only FinalizeClassesWithoutChildrenRector
```
