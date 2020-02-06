<?php

namespace Rector\Core\Tests\Issues\Issue1225\Fixture;

function issue1225()
{
    $safeTwigEnvironment = new \Twig_Environment(
        new \Twig_Loader_Array([])
    );
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue1225\Fixture;

function issue1225()
{
    $safeTwigEnvironment = new \Twig\Environment(
        new \Twig\Loader\ArrayLoader([])
    );
}

?>
