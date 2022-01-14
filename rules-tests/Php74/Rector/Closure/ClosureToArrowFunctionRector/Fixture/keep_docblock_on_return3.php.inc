<?php

function() {
    /**
     * comment
     */
    // something
    // @psalm-suppress UndefinedFunction
    return ff();
};
?>
-----
<?php

fn() => /**
     * comment
     */
// something
// @psalm-suppress UndefinedFunction
ff();
?>
