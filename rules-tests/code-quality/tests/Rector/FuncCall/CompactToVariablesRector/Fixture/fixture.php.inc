<?php

namespace Rector\CodeQuality\Tests\Rector\FuncCall\CompactToVariablesRector\Fixture;

use Rector\Testing\Contract\RunnableInterface;

final class Fixture implements RunnableInterface
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

namespace Rector\CodeQuality\Tests\Rector\FuncCall\CompactToVariablesRector\Fixture;

use Rector\Testing\Contract\RunnableInterface;

final class Fixture implements RunnableInterface
{
    public function run()
    {
        $checkout = 'one';
        $form = 'two';

        return ['checkout' => $checkout, 'form' => $form];
    }
}

?>
