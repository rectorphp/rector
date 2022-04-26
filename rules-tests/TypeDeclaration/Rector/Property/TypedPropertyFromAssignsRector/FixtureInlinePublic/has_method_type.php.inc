<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\FixtureInlinePublic;

final class HasMethodType
{
    public $obj;

    public function loader($obj)
    {
        if (! method_exists($obj, 'findFile')) {
            return;
        }

        return $this->obj = $obj;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\TypedPropertyFromAssignsRector\FixtureInlinePublic;

final class HasMethodType
{
    public object|string|null $obj = null;

    public function loader($obj)
    {
        if (! method_exists($obj, 'findFile')) {
            return;
        }

        return $this->obj = $obj;
    }
}

?>
