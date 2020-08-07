# Generate a new Rector Rule

Creating a new Rector rule means many small steps. You need to create rule file, test file, test fixtures, put it into right category, bla bla bla...

How can we **remove repeated work** and let us focus only on `refactor()` method?

## What `generate` Command is Does?

It creates a bare structured Rule.
Don't worry, also generates a test case, which is required to contribute.

## How to Generate Rector rule in 3 steps?

1. Copy [`create-rector.php.dist`](/../create-rector.php.dist) to `create-rector.php`
2. Change parameters in `create-rector.php` to meet you need
3. Run command

    ```bash
    vendor/bin/rector generate
    ```

That's it :)
