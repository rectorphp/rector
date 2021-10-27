<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeShmopOpen
{
    public function run()
    {
        $shm_key = ftok(__FILE__, 't');
        $shm_id = shmop_open($shm_key, "c", 0644, 100);

        is_resource($shm_id);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeShmopOpen
{
    public function run()
    {
        $shm_key = ftok(__FILE__, 't');
        $shm_id = shmop_open($shm_key, "c", 0644, 100);

        $shm_id instanceof \Shmop;
    }
}

?>
