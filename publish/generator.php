<?php

return [
    // 要生成的表名
    'table' => null,
    // 是否覆盖已有文件
    'force' => false,
    // 默认保存路径
    'path' => 'Service',
    // 自定义模板路径
    'templateDir' => '',
    // 校验器继承类
    'vBase' => 'App\\Validator\\BaseValidator',
    // 结构体继承类
    'sBase' => 'App\\Struct\\Base\\BaseStruct',
    // 模型继承类
    'mBase' => 'App\\Model\\Model',
    // 结构体继承类名
    'sBaseName' => 'BaseStruct',
    // 模型继承类名
    'mBaseName' => 'Model',
    // 校验器继承类名
    'vBaseName' => 'BaseValidator',
    // 数据库配置
    'dbConnectionName' => 'default',
    // 字段类型映射
    'varcharFieldMap' => ['varchar', 'char', 'text', 'mediumtext'],
    'enumFieldMap' => ['tinyint'],
    'timestampFieldMap' => ['date', 'datetime'],
    'numberFieldMap' => ['int'],
    'idFieldMap' => ['tinyint', 'smallint', 'mediumint', 'int', 'bigint'],
    // 字段类型匹配
    'createFieldMap' => [
        'createtime',
        'create_time',
        'createdtime',
        'created_time',
        'createat',
        'create_at',
        'createdat',
        'created_at'
    ],
    'updateFieldMap' => [
        'updatetime',
        'update_time',
        'updatedtime',
        'updated_time',
        'updateat',
        'update_at',
        'updatedat',
        'updated_at'
    ],
    'deleteFieldMap' => [
        'deletetime',
        'delete_time',
        'deletedtime',
        'deleted_time',
        'deleteat',
        'delete_at',
        'deletedat',
        'deleted_at'
    ],
    'passwordFieldMap' => ['password', 'pwd', 'encrypt'],
    'intFieldTypeList' => ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'serial'],
    'floatFieldTypeList' => ['decimal', 'float', 'double', 'real'],
];