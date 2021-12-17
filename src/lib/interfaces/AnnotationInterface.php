<?php


namespace iflow\annotation\lib\interfaces;

interface AnnotationInterface {

    // 注解运行入口
    public function process(&...$args): mixed;
}