<?php

namespace Rector\Core\Tests\Issues\Issue1243\Fixture;

class Issue1243
{
    public function something()
    {
        /** @var \Twig_Environment $env */
        $env = $this->getTwigEnv();
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue1243\Fixture;

class Issue1243
{
    public function something()
    {
        /** @var \Twig\Environment $env */
        $env = $this->getTwigEnv();
    }
}

?>
