<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\CompactToVariablesRector\Fixture;

final class Fixture
{
    public function run()
    {
        $checkout = 'one';
        $form = 'two';

        return compact('checkout', 'form');
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\FuncCall\CompactToVariablesRector\Fixture;

final class Fixture
{
    public function run()
    {
        $checkout = 'one';
        $form = 'two';

        return ['checkout' => $checkout, 'form' => $form];
    }
}

?>
