<?php


namespace iflow\annotation\lib\utils;

use iflow\annotation\lib\initializer\AnnotationInitializer;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionParameter;

class ObjectUtil {

    protected array $weakMap = [];

    protected static ?ObjectUtil $instance = null;

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

    public static function instance(): ObjectUtil {
        $instance = static::$instance;
        return $instance ?: self::setInstance(new static());
    }

    public static function setInstance(ObjectUtil $objectUtil): ObjectUtil {
        static::$instance = $objectUtil;
        return static::$instance;
    }

    public function getObject(string $class): object {
        if ($this->hasObject($class)) return $this->weakMap[$class];
        return $this->make($class);
    }

    public function make(&...$args): object {
        $class = array_shift($args);
        if (is_object($class)) {
            $this->weakMap[$class::class] = $class;
            return $class;
        }

        $args = [ 'classParameters' => $args ];
        $object = $this->getObject(AnnotationInitializer::class) -> execute($class, args: $args);
        return $this->weakMap[$class] = $object;
    }

    public function hasObject(string $class): bool {
        return !empty($this->weakMap[$class]);
    }
}