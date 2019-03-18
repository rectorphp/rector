<?php

namespace Rector\Tests\Issues\Issue1225\Fixture;

final class SomeController
{
    public function view($id = null)
    {
        $this->safeTwigEnvironment = new \Twig_Environment(
            new \Twig_Loader_Array([])
        );
    }
}

?>
-----
<?php

namespace Rector\Tests\Issues\Issue1225\Fixture;

final class SomeController
{
    public function view($id = null)
    {
        $this->safeTwigEnvironment = new \Twig\Environment(
            new \Twig\Loader\ArrayLoader([])
        );
    }
}

?>
