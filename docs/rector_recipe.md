# Generate a new Rector Rule

Creating a new Rector rule means many small steps. You need to create rule file, test file, test fixtures, put it into right category, bla bla bla...

How can we **remove repeated work** and let us focus only on `refactor()` method?

## What `generate` Command is Does?

It creates a bare structured Rule.
Don't worry, also generates a test case, which is required to contribute.

## How to Generate Rector rule?

There are two possibilities to Generate a Rector rule.

### Generate using Interactive Mode

**Important**: using this approach will generate Rector rule with placeholder Code Samples, which should be changed
by hand to reflect what the rule does

1. Run Generate command in Interactive Mode

    ```bash
   vendor/bin/rector generate --interactive
   ```

2. Provide an answer to questions asked by the command

### Generate using configuration file

1. Initialize `rector-recipe.php` config

    ```bash
    vendor/bin/rector init-recipe
    ```

2. Complete parameters in `rector-recipe.php` to design your new rule

3. Run command

    ```bash
    vendor/bin/rector generate
    ```

That's it :)
