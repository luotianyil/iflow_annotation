<?php

namespace iflow\annotation\lib\utils\enum;

enum AnnotationEnum: string {

    // 实例化前
    case beforeCreate = 'beforeCreate';
    // 实例化后
    case Created = 'Created';
    // 实例化参数
    case beforeMounted = 'beforeMounted';
    // 挂载完毕
    case Mounted = 'Mounted';

    public function getAnnotationLife(array $life, object $_self): array {
        match ($this) {
            AnnotationEnum::beforeCreate => array_push($life['beforeCreate'], $_self),
            AnnotationEnum::Created => array_push($life['Created'], $_self),
            AnnotationEnum::beforeMounted => array_push($life['beforeMounted'], $_self),
            AnnotationEnum::Mounted => array_push($life['Mounted'], $_self)
        };
        return $life;
    }
}