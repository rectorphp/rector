# Generating your own Rector from a Recipe

## 1. Configure a Rector Recipe in `rector.yaml`

```yaml
# rector.yaml
parameters:
    rector_recipe:
        # run "bin/rector create" to create a new Rector + tests from this config
        package: "Celebrity"
        name: "SplitToExplodeRector"
        node_types:
            # put the main node first, it is used to create the namespace
            - "Assign"
        description: "Removes unneeded $a = $a assignments"
        code_before: >
            <?php

            class SomeClass
            {
                public function run()
                {
                    $a = $a;
                }
            }

        code_after: >
            <?php

            class SomeClass
            {
                public function run()
                {
                }
            }

        source: # e.g. link to RFC or headline in upgrade guide, 1 or more in the list
            - ""
        set: "celebrity" # e.g. symfony30, target config to append this rector to
```

## 2. Generate it

```bash
vendor/bin/rector create
```

That's it :)
