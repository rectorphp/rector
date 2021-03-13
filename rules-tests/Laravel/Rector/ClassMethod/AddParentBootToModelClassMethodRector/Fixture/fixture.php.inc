<?php

namespace Rector\Tests\Laravel\Rector\ClassMethod\AddParentBootToModelClassMethodRector\Fixture;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function boot()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Laravel\Rector\ClassMethod\AddParentBootToModelClassMethodRector\Fixture;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    public function boot()
    {
        parent::boot();
    }
}

?>
