<?php

use iflow\annotation\lib\utils\ObjectUtil;

if (!file_exists('annotation_instance')) {
    function annotation_instance(string $name = ''): object {
        if ($name === '') return ObjectUtil::instance();
        return ObjectUtil::instance() -> getObject($name);
    }
}
