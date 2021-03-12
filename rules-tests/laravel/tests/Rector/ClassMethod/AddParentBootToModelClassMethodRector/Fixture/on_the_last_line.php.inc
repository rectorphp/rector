<?php

namespace Rector\Laravel\Tests\Rector\ClassMethod\AddParentBootToModelClassMethodRector\Fixture;

use Illuminate\Database\Eloquent\Model;

class OnTheLastLine extends Model
{
    public function boot()
    {
        $value = 1000;
        parent::boot();
    }
}

?>
-----
<?php

namespace Rector\Laravel\Tests\Rector\ClassMethod\AddParentBootToModelClassMethodRector\Fixture;

use Illuminate\Database\Eloquent\Model;

class OnTheLastLine extends Model
{
    public function boot()
    {
        parent::boot();
        $value = 1000;
    }
}

?>
