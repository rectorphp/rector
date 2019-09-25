<?php

class FqnizeNamespaced
{
    public function run()
    {
        return new DateTime();
    }
}

?>
-----
<?php

namespace Abc;

class FqnizeNamespaced
{
    public function run()
    {
        return new \DateTime();
    }
}

?>
