<?php


namespace iflow\annotation\lib\abstracts;


use iflow\annotation\lib\interfaces\AnnotationInterface;
use iflow\annotation\lib\utils\enum\AnnotationEnum;

abstract class AnnotationAbstract implements AnnotationInterface {

    /**
     * 注解执行顺序枚举
     * @var AnnotationEnum
     */
    public AnnotationEnum $hookEnum = AnnotationEnum::beforeCreate;

}
