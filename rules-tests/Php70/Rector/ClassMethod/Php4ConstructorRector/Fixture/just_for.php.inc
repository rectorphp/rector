<?php

declare(strict_types=1);

final class JustFor
{
    public function JustFor()
    {
        for ($i = 100; $i < 100; $i++) {
        }
    }
}

?>
-----
<?php

declare(strict_types=1);

final class JustFor
{
    public function __construct()
    {
        for ($i = 100; $i < 100; $i++) {
        }
    }
}

?>
