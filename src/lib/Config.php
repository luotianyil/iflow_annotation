<?php


namespace iflow\annotation\lib;


class Config {

    public function __construct(protected array $config = []) {
        $this->config = empty($this->config) ? include_once(__DIR__."../config.php") : $this->config;
    }

    /**
     * 获取生命周期
     * @param string $name
     * @return array|string
     */
    public function getHook(string $name = ''): array|string {
        if ($name === '') return $this->config['Hook'] ?? [];
        return $this->config['Hook'][$name] ?? [];
    }

    /**
     * 获取加载包体目录
     * @return array
     */
    public function getNameSpaces(): array {
        return $this->config['Namespaces'] ?? [];
    }

    /**
     * 获取项目根地址
     * @return string
     */
    public function getProjectRoot(): string {
        return $this->config['ProjectRoot'] ?? '';
    }

    /**
     * 获取缓存状态
     * @return bool
     */
    public function getCache(): bool {
        return $this->config['cache'] ?? false;
    }

    /**
     * 获取缓存地址
     * @return string
     */
    public function getCachePath(): string {
        return $this->config['cache_path'];
    }
}