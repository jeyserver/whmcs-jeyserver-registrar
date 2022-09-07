<?php

$jey_additionalfields = implode(
    DIRECTORY_SEPARATOR,
    array(ROOTDIR, "modules", "registrars", "jeyserver", "lib", "additionalfields.php")
);
if (file_exists($jey_additionalfields)) {
    include $jey_additionalfields;
}
