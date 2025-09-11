namespace App\Struct\Base;

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
        parent::__construct($input, $flags, $iteratorClass);
        $this->init($input);

        return $this;
    }

    /**
     * 静态获取Struct
     */
    public static function make(array $form): static
    {
        return new static($form);
    }

    // 驼峰转下划线
    public static function humpToUnderline($str): array|string|null
    {
        return preg_replace_callback('/([A-Z])/', function ($m) {
            return '_' . strtolower($m[0]);
        }, $str);
    }

    // 魔术调用
    public function __call($name, $arguments)
    {
        $tmp    = '';
        $name   = explode('_', self::humpToUnderline($name));
        $prefix = array_shift($name);
        $name   = implode('_', $name);
        if (in_array($prefix, ['get', 'set'])) {
            if ($arguments) {
                $this->$name = $arguments['0'];
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
     * 初始化数据
     * @param array $input
     * @return void
     */
    public function init(array $input): void
    {
        $fields = get_class_vars(static::class);
        foreach ($input as $k => $v) {
            if (array_key_exists($k, $fields)) {
                if (!is_null($fields[$k]) && gettype($fields[$k])) {
                    settype($v, gettype($fields[$k]));
                }
                $this->$k = $v;
                $this[$k] = $v;
            } else {
                $this->attach[$k] = $v;
                if (empty($this['attach'])) {
                    $this['attach'] = [];
                }
                $this['attach'][$k] = $v;
            }
        }
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
     * 检测扩展数据是否有
     * @return bool
     */
    public function hasAttach(): bool
    {
        return !empty($this->attach);
    }
}
