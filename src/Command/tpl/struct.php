declare (strict_types=1);

namespace App\Struct\<?= $nameSpace ?>;

use <?= $stBase ?>;

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
class <?= $modelName ?>Struct extends <?= $stBaseName ?>

{
    // 模型字段
<?php foreach ($numberField as $attr) { ?>
    public <?= $attr['COLUMN_TYPE'] ?> $<?= $attr['COLUMN_NAME'] ?> = <?= $attr['COLUMN_DEFAULT'] ?>; // <?= $attr['COLUMN_COMMENT'].PHP_EOL ?>
<?php } ?>
}
