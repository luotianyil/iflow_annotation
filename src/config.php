<?php

use iflow\annotation\lib\utils\hook\lib\{
    BeforeCreateHook,
    InitializerHook,
    CreatedHook
};

return [
    // 生命周期钩子
    'Hook' => [
        // 创建前
        'beforeCreate' => BeforeCreateHook::class,
        // 初始化对象
        'Initializer' => InitializerHook::class,
        // 对象实例化完毕后
        'Created' => CreatedHook::class
    ],
    // 需要读取的目录 <dir_name>
    'Namespaces' => [],
    'ProjectRoot' => __DIR__,
    // 目录缓存
    'cache' => false,
    // 缓存地址
    'cache_path' => __DIR__ . '/runtime/annotation/iflow_annotation'
];