<?php

namespace iflow\annotation\lib\utils\tools\tool;

use iflow\annotation\lib\abstracts\AnnotationAbstract;
use iflow\annotation\lib\initializer\AnnotationInitializer;
use iflow\annotation\lib\utils\enum\AnnotationEnum;

#[\Attribute(\Attribute::TARGET_CLASS)]
class Callback extends AnnotationAbstract {

    public AnnotationEnum $hookEnum = AnnotationEnum::Mounted;

    public function __construct(protected string $class = '', protected string $method = '') {
    }

    public function process(&...$args): mixed {
        // TODO: Implement process() method.

        $reflector = $args[0];
        $object = $args[count($args) - 1];

        if (function_exists($this->method)) {
            call_user_func($this->method, $reflector, $args[count($args) - 1], $args);
        }

        if (!class_exists($this->class)) throw new \Exception('Callback class does not exists', 502);

        if ($this->class === $reflector -> getName()) {
            return call_user_func([new $this -> class, $this->method], $reflector, $object, $args);
        }

        $_args = [ 'parameters' => [$reflector, $object, $args] ];
        return annotation_instance(AnnotationInitializer::class) -> execute($this -> class, $this->method, $_args);
    }
}