<?php
namespace Riven;

use ArrayObject;
use Hyperf\Collection\Arr;

/**
 * 基础结构体
 */
abstract class BaseStruct extends ArrayObject
{
    /**
     * 附加字段
     * @var array
     */
    public array $attach = [];

    /**
     * 初始化结构体数据
     * @param array  $input
     * @param int    $flags
     * @param string $iteratorClass
     */
    public function __construct(array $input = [], int $flags = 0, string $iteratorClass = "ArrayIterator")
    {
        parent::__construct([], $flags, $iteratorClass);
        $this->init($input);

        return $this;
    }

    /**
     * 初始化数据
     * @param array $input
     * @return void
     */
    public function init(array $input): void
    {
        foreach ($input as $key => $value) {
            if ($this->hasProperty($key)) {
                if ($propertyType = $this->getPropertyType($key)) {
                    settype($value, $propertyType);
                }
                $this->$key = $value;
                $this[$key] = $value;
            } else {
                $this->attach[$key] = $value;
                $this['attach'] = $this->attach;
            }
        }
    }

    /**
     * 静态获取Struct
     */
    public static function make(array $form): static
    {
        return new static($form);
    }

    /**
     * 魔术调用
     * @param $name
     * @param $arguments
     * @return bool|mixed|string
     */
    public function __call($name, $arguments)
    {
        $tmp    = '';
        $name   = explode('_', self::humpToUnderline($name));
        $prefix = array_shift($name);
        $name   = implode('_', $name);
        if (in_array($prefix, ['get', 'set'])) {
            if ($arguments) {
                $this->$name = $arguments['0'];
                $this[$name] = $arguments['0'];
            } else {
                $tmp = $this->$name;
            }
        }
        if ($prefix == 'has') {
            $tmp = !empty($this->$name);
        }

        return $tmp;
    }

    /**
     * 重写 offsetSet 方法确保同步
     */
    public function offsetSet($key, $value): void
    {
        if ($this->hasProperty($key)) {
            $this->$key = $value;
            parent::offsetSet($key, $value);
        } else {
            $this->attach[$key] = $value;
            parent::offsetSet('attach', $this->attach);
        }
    }

    /**
     * 重写 offsetUnset 方法确保同步
     */
    public function offsetUnset($key): void
    {
        unset($this->$key);
        parent::offsetUnset($key);
    }

    /**
     * 获取属性值的类型
     * @param string $property
     * @return string|null
     */
    public function getPropertyType(string $property): ?string
    {
        if ($this->hasProperty($property)) {
            return gettype($this->$property);
        }
        return null;
    }


    /**
     * 判断属性是否存在（包括继承的属性）
     * @param string $property
     * @return bool
     */
    public function hasProperty(string $property): bool
    {
        return property_exists($this, $property);
    }


    /**
     * 获取所有属性
     * @return array
     */
    public function getAll(): array
    {
        return get_object_vars($this);
    }

    /**
     * 获取子对象所有属性
     * @return array
     */
    public function getSubObjAll(): array
    {
        $objectVars = get_object_vars($this);
        unset($objectVars['attach']);

        return $objectVars;
    }

    /**
     * 追加扩展属性
     * @param array $value
     * @return BaseStruct
     */
    public function appendAttach(array $value): static
    {
        $this->attach = array_merge($this->attach, $value);

        return $this;
    }

    /**
     * 获取扩展属性
     * @param string                  $key
     * @param string|array|object|int $default
     * @return mixed
     */
    public function getAttach(string $key = '', string|array|object|int $default = ''): mixed
    {
        return Arr::get($this->attach, $key) ?? $default;
    }

    /**
     * 驼峰转下划线
     * @param $str
     * @return array|string|null
     */
    public static function humpToUnderline($str): array|string|null
    {
        return preg_replace_callback('/([A-Z])/', fn ($m) => '_' . strtolower($m[0]), $str);
    }
}
