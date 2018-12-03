<?php

function phpstormVarAnnotation()
{
    $value = $this->getSomeType();
    /* @var $value \SomeType */

    $value2 = $this->getSomeType();
    /* @var \SomeType $value2 */
}

?>
-----
<?php

function phpstormVarAnnotation()
{
    /** @var \SomeType $value */
    $value = $this->getSomeType();
    /** @var \SomeType $value2 */
    $value2 = $this->getSomeType();
}

?>
