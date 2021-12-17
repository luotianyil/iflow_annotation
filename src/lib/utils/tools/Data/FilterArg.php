<?php

namespace iflow\annotation\lib\utils\tools\Data;

use iflow\annotation\lib\initializer\AnnotationInitializer;
use iflow\annotation\lib\utils\tools\abstracts\ValueAnnotationAbstract;

#[\Attribute(\Attribute::TARGET_PARAMETER|\Attribute::TARGET_PROPERTY)]
class FilterArg extends ValueAnnotationAbstract {

    public function __construct(
        protected mixed $called,
        protected array $calledParams = [],
        protected string $name = ''
    ) {}

    public function process(&...$args): mixed {
        // TODO: Implement process() method.
        $ref = $args[0];
        $object = $ref instanceof \ReflectionParameter ? null : $args[count($args) - 1];
        $value = $this->getValue($ref, $object, $args);

        if ($ref instanceof \ReflectionProperty) {
            $ref -> setValue($object, $this->called($value));
            return $ref -> getValue($object);
        } else {
            $index = $ref -> getPosition();
            return $args[$index] = $this->called($args[$index] ?: '');
        }
    }

    protected function called($closure): mixed {
        // 验证是否为闭包
        if ($closure instanceof \Closure) return call_user_func($closure, ...$this->calledParams);
        // 验证方法是否存在
        if (function_exists($closure)) return call_user_func($closure, ...$this->calledParams);

        // 验证是否为类
        $closure = explode('@', $closure);
        if (count($closure) < 2 || !class_exists($closure[0])) return null;

        return annotation_instance(AnnotationInitializer::class) -> execute($closure[0], $closure[1], [
            'parameters' => $this -> calledParams
        ]) ;
    }
}