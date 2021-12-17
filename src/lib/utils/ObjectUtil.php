<?php


namespace iflow\annotation\lib\utils;

use {
    ReflectionNamedType,
    ReflectionProperty,
    ReflectionParameter,
    ReflectionClass,
    WeakMap
};

class ObjectUtil {

    // 临时对象存储
    protected WeakMap $weakMap;

    /**
     * 获取参数类型
     * @param ReflectionProperty|ReflectionParameter $property
     * @return array
     */
    public function getParameterType(ReflectionProperty|ReflectionParameter $property): array {
        $type = $property -> getType();
        $types = [];
        if ($type instanceof \ReflectionUnionType) {
            foreach ($type -> getTypes() as $t) {
                $types[] = $t -> getName();
            }
        } else if ($type instanceof ReflectionNamedType) {
            $types[] = $type -> getName();
        }
        return $types ?: ['mixed'];
    }
}