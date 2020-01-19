<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Echo_\CakePHPTemplateHToTwigRector\Fixture;

3><?php echo h($value); ?></h3>

?>
-----
<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Echo_\CakePHPTemplateHToTwigRector\Fixture;

3>{{ value|escape }}</h3>

?>
