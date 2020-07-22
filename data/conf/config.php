<?php

if (file_exists(CMF_ROOT . "data/conf/option.php")) {
    $option = include CMF_ROOT . "data/conf/option.php";
} else {
    $option = [];
}

return [
    'option' => $option
];
