<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeEnhanceBrokerInit
{
    public function run()
    {
        $ch = enchant_broker_init();
        is_resource($ch);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomeEnhanceBrokerInit
{
    public function run()
    {
        $ch = enchant_broker_init();
        $ch instanceof \EnchantBroker;
    }
}

?>
