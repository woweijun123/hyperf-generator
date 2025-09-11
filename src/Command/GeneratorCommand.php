<?php

declare(strict_types=1);

namespace Riven\Command;

use Exception;
use Hyperf\Collection\Arr;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Stringable\Str;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption as Option;
use Symfony\Component\Console\Output\OutputInterface;

#[Command]
class GeneratorCommand extends HyperfCommand
{
    /**
     * 数据库连接
     */
    public static string $dbConnectionName = 'default';
    /**
     */
    #[Inject]
    protected ConfigInterface $config;

    public function __construct()
    {
        parent::__construct();
        $this->setName('generate')
             ->addOption('table', 't', Option::VALUE_OPTIONAL, '要生成的table，多个用,隔开, 默认为所有table')
             ->addOption(
                 'type',
                 null,
                 Option::VALUE_OPTIONAL,
                 "要生成的类型，多个用,隔开,如 c,v,s,d,m,st\n c -- controller, v -- validate, s -- service, d -- data, m -- model, -st -- struct"
             )
             ->addOption('path', 'p', Option::VALUE_OPTIONAL, '文件放置的路径')
             ->addOption('force', 'f', Option::VALUE_NONE, "覆盖已存在文件")
             ->setDescription('自动生成结构体');
    }

    /**
     * 执行
     * @return void
     */
    public function handle(): void
    {
        $output = $this->output;
        if (!($config = $this->parseConfig($this->input))) {
            return;
        }
        // 检查并创建 BaseStruct 文件
        $this->checkAndCreateBaseStruct($config);
        $tableList = self::dbQuery('SHOW TABLES');
        foreach ($tableList as $table) {
            $tableName = reset($table);
            // 非指定表名跳过
            if (!is_null($config['table']) && !in_array($tableName, $config['table'])) {
                continue;
            }
            // 查询字段
            $tableColumns = self::dbQuery(
                'SELECT * FROM `information_schema`.`columns` WHERE `table_schema` = ? AND `table_name` = ? ORDER BY `ordinal_position`',
                [$config['databaseName'], $tableName]
            );
            // 查询表注释
            $tableDesc = self::dbQuery(
                'SELECT `TABLE_COMMENT` FROM `information_schema`.`TABLES` WHERE `table_schema` = ? AND `table_name` = ?',
                [$config['databaseName'], $tableName]
            );
            // 获取表注释
            $tableDesc = Arr::get(reset($tableDesc), 'TABLE_COMMENT');
            // 主键
            $pk = '';
            // 创建时间字段
            $createTime = false;
            // 更新时间字段
            $updateTime = false;
            // 软删除字段
            $deleteTime = false;
            // 新增、编辑请求字段过滤
            $fieldStr = '';
            // 可填充字段
            $fillableField = [];
            // 强制转换字段
            $castField = [];
            // 校验器字段
            $validateStr = '';
            // 模型名
            $modelName = Str::studly($tableName);
            $modelInstanceName = lcfirst($modelName . 'Instance');
            // 模型属性「数组」
            $numberField = [];
            $generateResult = [];
            foreach ($tableColumns as &$field) {
                // 当前字段名称
                $name = $field['COLUMN_NAME'];
                // 字段名称转大写
                $field['COLUMN_NAME_UPPER'] = Str::studly($name);
                // 数据库类型转模型类型
                $this->transformType($field, $config);
                // 模型属性「数组」
                $numberField[] = [
                    'COLUMN_NAME' => $field['COLUMN_NAME'],
                    'COLUMN_NAME_UPPER' => $field['COLUMN_NAME_UPPER'],
                    'COLUMN_TYPE' => $field['PHP_TYPE'],
                    'DATA_TYPE' => $field['DATA_TYPE'],
                    'COLUMN_COMMENT' => $field['COLUMN_COMMENT'],
                    'COLUMN_DEFAULT' => $field['DEFAULT_VALUE'],
                ];
                // 构建数据
                $this->buildData(
                    $field, $name, $pk, $config, $createTime, $updateTime, $deleteTime, $fieldStr, $fillableField, $castField, $validateStr
                );
            }
            $path = $config['path'] ?: $modelName;
            // 生成模型、校验器、控制器
            $data = [
                // 模型 表的数据库前缀
                'dbNamePrefix'   => '',
                'pk'             => $pk,
                'pkCamel'        => self::underlineToHumpTwo($pk),
                'tableName'      => $tableName,
                'tableDesc'      => $tableDesc,
                'tableColumns'   => $tableColumns,
                'numberField'    => $numberField,
                'modelName'      => $modelName,
                'modelNameCamel' => lcfirst($modelName),
                'modelAlias'     => $modelName . 'Model',
                'modelInstance'  => $modelInstanceName,
                'validateEnable' => true,
                'validateAlias'  => $modelName . 'Validate',
                'createTime'     => $createTime,
                'updateTime'     => $updateTime,
                'deleteTime'     => $deleteTime,
                'autoTime'       => $createTime || $updateTime || $deleteTime,
                'fieldStr'       => $fieldStr,
                'castField'      => $castField,
                'fillableField'  => $fillableField,
                'validateStr'    => $validateStr,
                'nameSpace'      => strtr($path, ['/' => '\\']),
            ];
            // 文件内容
            $context = array_merge($config, $data);
            // 文件模板路径
            $templateDir = $config['templateDir'] ?: $this->tpmPath();
            // 文件模板映射关系
            $templateFile = [
                'struct' => self::appPath() . "Struct/$path/{$modelName}Struct.php",
                'model' => self::appPath() . "Model/$path/{$modelName}Model.php",
                'validate' => self::appPath() . "Validator/$path/{$modelName}Validator.php",
                'controller' => self::appPath() . "Controller/$path/{$modelName}Controller.php",
                'service' => self::appPath() . "Service/$path/{$modelName}Service.php",
                'data' => self::appPath() . "Data/$path/{$modelName}Data.php",
            ];
            // 是否调试
            if ($output->isDebug()) {
                self::writeBlock($output, [
                    $tableName . " data:",
                    json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                ]);
            }
            $generateResult['table'] = $tableName;
            // 生成对应文件
            foreach ($templateFile as $key => $path) {
                if (!empty($config['type']) && !in_array($key, $config['type'])) {
                    continue;
                }
                $content = self::compile("$templateDir$key.php", $context);
                if (is_file($path) && !$config['force']) {
                    $generateResult[$key] = 'File exists';
                    continue;
                }
                $dirName = dirname($path);
                if (!is_dir($dirName)) {
                    mkdir($dirName, 0777, true);
                }
                file_put_contents($path, "<?php\n$content");
                $generateResult[$key] = 'Generated';
            }
            // 打印到命令行
            $this->table(array_keys($generateResult), [array_values($generateResult)]);
        }
    }

    /**
     * 检查并创建 BaseStruct 文件
     * @param array $config
     * @return void
     */
    private function checkAndCreateBaseStruct(array $config): void
    {
        $path = self::appPath() . 'Struct/Base/BaseStruct.php';
        // 如果 BaseStruct 文件已存在，则直接返回
        if (file_exists($path)) {
            return;
        }
        // 创建目录
        $dirName = dirname($path);
        if (!is_dir($dirName)) {
            mkdir($dirName, 0777, true);
        }
        // BaseStruct 文件内容
        $content = file_get_contents($this->tpmPath() . 'BaseStruct.php');
        // 写入文件
        file_put_contents($path, $content);
        file_put_contents($path, "<?php\n$content");

        $this->output->writeln('<info>BaseStruct file created successfully.</info>');
    }

    /**
     * 解析配置
     * @param InputInterface $input
     * @return array
     */
    public function parseConfig(InputInterface $input): array
    {
        // 加载配置文件
        $commandConfig = $this->config->get('generator');
        if (empty($commandConfig)) {
            throw new Exception('请先创建配置文件 generator.php');
        }
        $commandConfig['databaseName'] = $this->config->get("databases.{$commandConfig['dbConnectionName']}.database");
        self::$dbConnectionName = $commandConfig['dbConnectionName'] ?? '';

        $typeList = $input->getOption('type') ? explode(',', $input->getOption('type')) : $commandConfig['type'];
        $typeLang = ['c' => 'controller', 'v' => 'validate', 's' => 'service', 'st' => 'struct', 'd' => 'data', 'm' => 'model'];
        foreach ($typeList as &$type) {
            if (isset($typeLang[$type])) {
                $type = $typeLang[$type];
            }
        }
        $args = [
            // 要生成的表名
            'table' => $input->getOption('table') ? explode(',', $input->getOption('table')) : $commandConfig['table'],
            // 是否覆盖已有文件
            'force' => $input->getOption('force'),
            // 模型继承类
            'path' => $input->getOption('path') ?: $commandConfig['path'],
            // 要生成的类型
            'type' => $typeList,
        ];
        return array_merge($commandConfig, $args);
    }

    /**
     * 查询数据库
     * @param string $query
     * @param array $bindings
     * @return array
     */
    public static function dbQuery(string $query, array $bindings = []): array
    {
        return array_map(function ($v) {
            return (array)$v;
        }, Db::connection(self::$dbConnectionName)->select($query, $bindings));
    }

    /**
     * 复数转单数
     * @param mixed $value
     * @return string
     */
    public static function singular(mixed $value): string
    {
        if (str_contains($value, 'goodses')) {
            return str_replace('goodses', 'goods', $value);
        }
        if (str_contains($value, 'contracts')) {
            return str_replace('contracts', 'contract', $value);
        }
        return Str::singular($value);
    }

    /**
     * 数据库类型转模型类型
     * @param mixed $field
     * @param array $config
     * @return void
     */
    private function transformType(mixed &$field, array $config): void
    {
        switch ($field['DATA_TYPE']) {
            case in_array($field['DATA_TYPE'], $config['int_type']):
                $field['PHP_TYPE']      = 'int';
                $field['DEFAULT_VALUE'] = $field['COLUMN_DEFAULT'] ?? 0;
                break;
            case in_array($field['DATA_TYPE'], $config['float_type']):
                $field['PHP_TYPE']      = 'float';
                $field['DEFAULT_VALUE'] = $field['COLUMN_DEFAULT'] ?? 0;
                break;
            case in_array($field['DATA_TYPE'], $config['bool_type']):
                $field['PHP_TYPE']      = 'boolean';
                $field['DEFAULT_VALUE'] = $field['COLUMN_DEFAULT'] ?? false;
                break;
            case $field['DATA_TYPE'] == 'json':
                $field['PHP_TYPE']      = 'mixed';
                $field['DEFAULT_VALUE'] = $field['COLUMN_DEFAULT'] ?? '[]';
                break;
            case $field['DATA_TYPE'] == 'datetime':
                $field['PHP_TYPE']      = '?string';
                $field['DEFAULT_VALUE'] = $field['COLUMN_DEFAULT'] ?? 'null';
                break;
            case $field['DATA_TYPE'] == 'point':
                $field['PHP_TYPE']      = '?string';
                $field['DEFAULT_VALUE'] = "''";
                break;
            default:
                $field['PHP_TYPE']      = 'string';
                $field['DEFAULT_VALUE'] = !empty($field['COLUMN_DEFAULT']) ? "'{$field['COLUMN_DEFAULT']}'" : "''";
                break;
        }
    }

    /**
     * 构建数据
     * @param mixed $field
     * @param mixed $name
     * @param mixed $pk
     * @param array $config
     * @param mixed $createTime
     * @param mixed $updateTime
     * @param mixed $deleteTime
     * @param string $fieldStr
     * @param array $fillableField
     * @param array $castField
     * @param string $validateStr
     * @return void
     */
    private function buildData(
        mixed  $field,
        mixed  $name,
        mixed  &$pk,
        array  $config,
        mixed  &$createTime,
        mixed  &$updateTime,
        mixed  &$deleteTime,
        string &$fieldStr,
        array &$fillableField,
        array &$castField,
        string &$validateStr
    ): void
    {
        // 添加批量赋值字段
        $fillableField[] = "'$name'";
        // 自动转换字段
        switch ($field['PHP_TYPE']) {
            case 'mixed':
                $castType = 'array';
                break;
            case 'int':
                if ($field['DATA_TYPE'] == 'bigint') {
                    $castType = 'string';
                } else {
                    $castType = 'integer';
                }
                break;
            case '?string':
                $castType = 'datetime';
                break;
            default:
                $castType = $field['PHP_TYPE'];
                break;
        }

        $castField[] = "'$name' => '$castType'";
        // 判断是否为主键
        if ($field['COLUMN_KEY'] == 'PRI') {
            $pk = $name;
        } else {
            // 判断时间字段
            $isTimeField = false;
            if (self::arrayLikeCase($name, $config['create_field']) !== false) {
                $createTime = $name;
                $isTimeField = true;
            } else {
                if (self::arrayLikeCase($name, $config['update_field']) !== false) {
                    $updateTime = $name;
                    $isTimeField = true;
                } else {
                    if (self::arrayLikeCase($name, $config['delete_field']) !== false) {
                        $deleteTime = $name;
                        $isTimeField = true;
                    }
                }
            }
            if (!$isTimeField) {
                $defaultValue = self::parseFieldDefaultValue($field['DATA_TYPE'], $field['COLUMN_DEFAULT'] ?? '');
                $fieldStr .= "'$name' => $defaultValue,\n";
            }
            // 非时间字段加入校验器
            if (!$isTimeField) {
                $validateKey = $name . ($field['COLUMN_COMMENT'] ? "|{$field['COLUMN_COMMENT']}" : '');
                $validateValue = '';

                if (!is_null($field['CHARACTER_MAXIMUM_LENGTH'])) {
                    $validateValue .= "|max:{$field['CHARACTER_MAXIMUM_LENGTH']}";
                }

                if (in_array($field['DATA_TYPE'], $config['int_type'])) {
                    $validateValue .= "|integer";
                }

                if ($validateValue) {
                    $validate[$validateKey] = ltrim($validateValue, '|');
                    foreach ($validate as $key => $value) {
                        $validateStr .= "'$key' => '$value',\n";
                    }
                }

            }
        }
    }

    /**
     * 不区分大小写的查找
     * @param mixed $needle
     * @param array $haystack
     * @return boolean
     */
    public static function arrayLikeCase(mixed $needle, array $haystack): bool
    {
        foreach ($haystack as $key => $value) {
            if (str_contains(strtolower($needle), $value)) {
                return (bool)$key;
            }
        }
        return false;
    }

    /**
     * 返回默认值
     * @param string $type
     * @param mixed $default
     * @return string|int|float|null
     */
    public static function parseFieldDefaultValue(string $type, mixed $default): string|int|null|float
    {
        return match (strtolower($type)) {
            'tinyint', 'smallint', 'mediumint', 'int' => (int)$default,
            'float', 'double', 'decimal' => (float)$default,
            default => var_export($default, true),
        };
    }

    /**
     * 获取app路径
     *
     * @return string
     */
    public static function appPath(): string
    {
        return BASE_PATH . '/app/';
    }

    /**
     * 输出信息块
     * @param OutputInterface $output
     * @param mixed $message
     * @return void
     */
    public static function writeBlock(OutputInterface $output, mixed $message): void
    {
        $output->writeln('');
        foreach ((array)$message as $msg) {
            $output->writeln($msg);
        }
        $output->writeln('');
    }

    /**
     * 编译php模板文件
     *
     * @param string $templatePath
     * @param array $context
     * @return string
     */
    public static function compile(string $templatePath, array $context): string
    {
        extract($context);
        ob_start();
        include_once $templatePath;
        $res = ob_get_contents();
        ob_end_clean();
        return $res;
    }

    /**
     * 下划线转大驼峰
     */
    public static function underlineToHumpTwo($str): array|string|null
    {
        return strtr(ucwords(strtr($str, ['_' => ' '])), [' ' => '']);
    }

    /**
     * @return string
     */
    protected function tpmPath(): string
    {
        return __DIR__ . DIRECTORY_SEPARATOR . 'tpl' . DIRECTORY_SEPARATOR;
    }
}

