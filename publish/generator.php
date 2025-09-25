<?php

return [
    // 要生成的表名
    'table' => null,
    // 要生成的类型
    'type'   => [], // c,v,s,d,m,st
    // 是否覆盖已有文件
    'force' => false,
    // 默认保存路径
    'path' => '',
    // 自定义模板路径
    'templateDir' => '',
    // 控制器继承类
    'cBase' => 'Riven\\BaseController',
    // 结构体继承类
    'sBase' => 'Riven\\BaseStruct',
    // 校验器继承类
    'vBase' => 'App\\Validator\\BaseValidator',
    // 模型继承类
    'mBase' => 'Riven\\BaseModel',
    // 控制器继承类名
    'cBaseName' => 'AbstractController',
    // 结构体继承类名
    'sBaseName' => 'BaseStruct',
    // 校验器继承类名
    'vBaseName' => 'BaseValidator',
    // 模型继承类名
    'mBaseName' => 'BaseModel',
    // 数据库配置
    'dbConnectionName' => 'default',
    // 字段类型映射
    'varchar_field'    => ['varchar', 'char', 'text', 'mediumtext'],
    'enum_field'       => ['tinyint'],
    'timestamp_field'  => ['date', 'datetime'],
    'number_field'     => ['int'],
    'id_field'         => ['tinyint', 'smallint', 'mediumint', 'int', 'bigint'],
    // 字段类型匹配
    'create_field'     => ['createtime', 'create_time', 'createdtime', 'created_time', 'createat', 'create_at', 'createdat', 'created_at'],
    'update_field'     => ['updatetime', 'update_time', 'updatedtime', 'updated_time', 'updateat', 'update_at', 'updatedat', 'updated_at'],
    'delete_field'     => ['deletetime', 'delete_time', 'deletedtime', 'deleted_time', 'deleteat', 'delete_at', 'deletedat', 'deleted_at'],
    'int_type'         => ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'serial'],
    'float_type'       => ['decimal', 'float', 'double', 'real'],
    'bool_type'        => ['bool', 'boolean'],
    // 操作符号
    'bigint_symbol'   => ['in' => 'In', 'not_in' => 'NotIn'],
];