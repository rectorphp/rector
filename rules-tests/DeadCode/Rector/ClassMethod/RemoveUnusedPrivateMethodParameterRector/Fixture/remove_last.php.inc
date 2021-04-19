<?php

final class RemoveLast
{
    private function run(string $used, int $notUsed)
    {
        echo $used;
    }
}

?>
-----
<?php

final class RemoveLast
{
    private function run(string $used)
    {
        echo $used;
    }
}

?>
