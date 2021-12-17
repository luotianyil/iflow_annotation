<?php

namespace iflow\annotation\lib\utils\tools\Data;

use iflow\annotation\lib\utils\tools\abstracts\ValueAnnotationAbstract;

#[\Attribute]
class NotNull extends ValueAnnotationAbstract {

    public function __construct(
        protected mixed $value = "",
        protected string $error = ""
    ) {}

    public function process(&...$args): bool {
        // TODO: Implement process() method.
        try {
            // 获取初始化值
            $ref = $args[0];
            $object = $ref instanceof \ReflectionParameter ? null : $args[count($args) - 1];
            $value = $this->getValue($ref, $object, $args);

            $type = annotation_instance() -> getParameterType($ref);

            if (in_array('array', $type) && empty($type)) $this->throw_error($ref);
            if (!is_null($value) && $value !== '') return true;
            $this->throw_error($ref);
        } catch (\Error) {
            $this->throw_error($ref);
        }
    }
}