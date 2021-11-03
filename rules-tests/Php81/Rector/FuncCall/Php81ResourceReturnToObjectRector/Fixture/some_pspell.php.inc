<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomePspell
{
    public function run()
    {
        $pspell_config = pspell_config_create("en");
        is_resource($pspell_config);
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\Php8ResourceReturnToObjectRector\Fixture;

final class SomePspell
{
    public function run()
    {
        $pspell_config = pspell_config_create("en");
        $pspell_config instanceof \PSpell\Config;
    }
}

?>
