<?php

use function Safe\count;

class ImportFunctionWithoutNamespace
{
    public function run()
    {
        return \Safe\count([1]);
    }
}

?>
-----
<?php

use function Safe\count;

class ImportFunctionWithoutNamespace
{
    public function run()
    {
        return count([1]);
    }
}

?>
