declare (strict_types=1);

namespace App\Model\<?= $nameSpace ?>;

use <?=$mBase?>;
<?php if ($useSnowflakeId){ ?>
    use Hyperf\Snowflake\Concern\Snowflake;
<?php } ?>
<?php if ($deleteTime){ ?>
    use Hyperf\Database\Model\SoftDeletes;
<?php } ?>

/**
* <?= $modelName ?> Model of <?= $tableDesc.PHP_EOL ?>
<?php foreach ($numberField as $property) { ?>
    * @property <?= $property['COLUMN_TYPE'] ?> $<?= $property['COLUMN_NAME'] ?> <?= $property['COLUMN_COMMENT'].PHP_EOL ?>
<?php } ?>
*/
class <?=$modelName?>Model extends <?=$mBaseName?>

{
<?php if ($useSnowflakeId){ ?>
    use Snowflake;
<?php } ?>
<?php if ($deleteTime){ ?>
    use SoftDeletes;
<?php } ?>
<?php if ($createTime){ ?>

    /**
    * 创建时间
    */
    const CREATED_AT = '<?=$createTime?>';
<?php }?>
<?php if ($updateTime){ ?>

    /**
    * 更新时间
    */
    const UPDATED_AT = '<?=$updateTime?>';
<?php }?>
<?php if ($deleteTime){ ?>

    /**
    * 删除时间
    */
    const DELETED_AT = '<?=$deleteTime?>';
<?php }?>

    /**
    * 数据表名称
    */
    protected ?string $table = '<?=$dbNamePrefix?><?=$tableName?>';

    /**
    * 数据表主键 复合主键使用数组定义
    */
    protected string $primaryKey = '<?=$pk?>';
<?php if ($autoTime){ ?>

    /**
    * 是否需要自动写入时间戳
    */
    public bool $timestamps = true;
<?php } else { ?>

    /**
    * 是否自动管理时间戳
    */
    public bool $timestamps = false;
<?php }?>

    /**
    * 可批量赋值的字段
    */
    protected array $fillable = [
<?php foreach ($fillableField as $fillableFieldV) { ?>
        <?= $fillableFieldV. ','.PHP_EOL ?>
<?php } ?>
    ];

    /**
    * 自动转换属性字段
    */
    protected array $casts = [
<?php foreach ($castField as $castFieldV) { ?>
        <?= $castFieldV. ','.PHP_EOL ?>
<?php } ?>
    ];
}
