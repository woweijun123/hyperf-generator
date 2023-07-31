declare (strict_types=1);

namespace App\Struct\<?= $nameSpace ?>;

use <?= $sBase ?>;

/**
* <?= $modelName ?> Struct of <?= $tableDesc.PHP_EOL ?>
<?php foreach ($numberField as $property) { ?>
* @property <?= $property['COLUMN_TYPE'] ?> $<?= $property['COLUMN_NAME'] ?> <?= $property['COLUMN_COMMENT'].PHP_EOL ?>
<?php } ?>
<?php foreach ($numberField as $property) { ?>
* @method set<?= strtr(ucwords(strtr($property['COLUMN_NAME'], ['_' => ' '])),[' ' => '']) ?>(<?= $property['COLUMN_TYPE'] ?> $<?= lcfirst(strtr(ucwords(strtr($property['COLUMN_NAME'], ['_' => ' '])),[' ' => ''])) ?>)
* @method get<?= strtr(ucwords(strtr($property['COLUMN_NAME'], ['_' => ' '])),[' ' => '']) ?>()
* @method has<?= strtr(ucwords(strtr($property['COLUMN_NAME'], ['_' => ' '])),[' ' => '']) ?>()
<?php } ?>
*/
class <?= $modelName ?>Struct extends <?= $sBaseName ?>

{
    // 模型字段
<?php foreach ($numberField as $attr) { ?>
    public <?= $attr['COLUMN_TYPE'] ?> $<?= $attr['COLUMN_NAME'] ?> = <?= $attr['COLUMN_DEFAULT'] ?>; // <?= $attr['COLUMN_COMMENT'].PHP_EOL ?>
<?php } ?>
<?php if (!method_exists('App\Struct\Base\\' . $sBaseName, 'humpToUnderline')) { ?>
    // 驼峰转下划线
    public static function humpToUnderline($str): array|string|null
    {
        return preg_replace_callback('/([A-Z])/', function ($m) {
            return '_' . strtolower($m[0]);
        }, $str);
    }
<?php } ?>
<?php if (!method_exists('App\Struct\Base\\' . $sBaseName, '__call')) { ?>
    // 魔术调用
    public function __call($name, $arguments)
    {
        $tmp = '';
        $name = explode('_', self::humpToUnderline($name));
        $prefix = array_pop($name);
        $name = implode('_', $name);
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
<?php } ?>
}
