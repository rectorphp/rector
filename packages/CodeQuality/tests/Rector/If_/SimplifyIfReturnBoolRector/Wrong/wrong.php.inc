<?php

if (strpos($docToken->getContent(), "\n") === false) {
    return true;
}

return false;



if (strpos($docToken->getContent(), "\n") !== false) {
    return true;
}

return false;


if (strpos($docToken->getContent(), "\n") !== false) {
    return true;
}

return 5;
