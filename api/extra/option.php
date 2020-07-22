<?php
/**
 * 第三方配置KEY
 */
if (file_exists(CMF_ROOT . "data/conf/option.php")) {
    $confDefine = include CMF_ROOT . "data/conf/option.php";
} else {
    $confDefine = [];
}

return $confDefine;