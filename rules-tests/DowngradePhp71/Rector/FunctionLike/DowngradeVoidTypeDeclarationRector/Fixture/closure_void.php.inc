<?php

$callable = function (): void {
    return;
};

?>
-----
<?php

$callable = function () {
    return;
};

?>
