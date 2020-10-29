# How To Run Rector on Changed Files Only

Execution can be limited to changed files using the `process` option `--match-git-diff`.
This option will filter the files included by the configuration, creating an intersection with the files listed in `git diff`.

```bash
vendor/bin/rector process src --match-git-diff
```

This option is useful in CI with pull-requests that only change few files.
