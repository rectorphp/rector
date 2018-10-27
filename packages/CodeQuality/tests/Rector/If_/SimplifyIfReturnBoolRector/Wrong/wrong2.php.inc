<?php

if (strpos($docToken->getContent(), "\n")) {
    return false;
}

return true;

if (!strpos($docToken->getContent(), "\n")) {
    return true;
}

return false;

if (!strpos($docToken->getContent(), "\n")) {
    return true;
}

return 5;
