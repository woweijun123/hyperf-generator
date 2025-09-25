<?php

namespace Riven;

use Exception;
use Hyperf\Collection\Collection;
use Hyperf\Database\Model\Builder;
use Hyperf\Database\Model\Model;
use Hyperf\Paginator\LengthAwarePaginator;

use function Hyperf\Support\value;

abstract class BaseData
{
    /**
     * 新增单条数据
     * @param array $data
     * @return Model|null
     */
    public function insertOne(array $data): ?Model
    {
        if (!($model = $this->model->newQuery()->create($data))) {
            throw new Exception('Failed to add new data');
        }

        return $model;
    }

    /**
     * 新增多条数据
     * @param array $attributes
     * @return bool
     */
    public function insertMany(array $attributes = []): bool
    {
        foreach ($attributes as &$attribute) {
            $this->model->optimizer($attribute);
        }

        return $this->model->newQuery()->insert($attributes);
    }

    /**
     * 根据主键删除数据
     * @param string|int $pk
     * @return int
     */
    public function deleteByPK(string|int $pk): int
    {
        if (empty($pk)) {
            return 0;
        }

        return $this->model::query()->where($this->model->getKeyName(), $pk)->delete();
    }

    /**
     * 根据外键删除数据
     * @param string     $foreignKey
     * @param string|int $pk
     * @return int
     */
    public function deleteByForeignKey(string $foreignKey, string|int $pk): int
    {
        if (empty($pk)) {
            return 0;
        }

        return $this->model::query()->where($foreignKey, $pk)->delete();
    }

    /**
     * 根据外键删除数据
     * @param string $foreignKey
     * @param array  $ids
     * @return int
     */
    public function deleteByForeignKeys(string $foreignKey, array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        return $this->model::query()->whereIn($foreignKey, $ids)->delete();
    }

    /**
     * 根据条件删除数据
     * @param array $where
     * @return int
     */
    public function deleteByWhere(array $where): int
    {
        if (empty($where)) {
            return 0;
        }

        return $this->parseWhere($where)->delete();
    }

    /**
     * 根据主键ID批量删除数据
     * @param array $ids
     * @return int
     */
    public function deleteByIds(array $ids): int
    {
        if (empty($ids)) {
            return 0;
        }

        return $this->model::query()->whereIn($this->model->getKeyName(), $ids)->delete();
    }

    /**
     * 更新数据
     * @param array $where
     * @param array $data
     * @return int
     */
    public function updateData(array $where = [], array $data = []): int
    {
        return $this->model->optimizer($data)->where($where)->update($data);
    }

    /**
     * 增加
     * @param array      $where
     * @param string     $column
     * @param string|int $amount
     * @return int
     */
    public function incr(array $where, string $column, string|int $amount = 1): int
    {
        return $this->parseWhere($where)->increment($column, $amount);
    }

    /**
     * 减少
     * @param array      $where
     * @param string     $column
     * @param string|int $amount
     * @return int
     */
    public function decr(array $where, string $column, string|int $amount): int
    {
        return $this->parseWhere($where)->decrement($column, $amount);
    }

    /**
     * 解析 ThinkPHP/Laravel 混合风格的 where 查询条件。
     * // 使用方式示例
     * $where = [
     *     // 等值查询
     *     ['id', '=', 1]
     *     ['name' => 'john', 'status' => 1],
     *     // 运算符查询
     *     ['name', '<>', 'tom']
     *     ['age', '>', 18]
     *     ['age', '>=', 20]
     *     ['age', '<', 30]
     *     ['age', '<=', 40]
     *     // 模糊查询
     *     ['email', 'like', '%@example.com']
     *     // IN/NOT IN查询
     *     ['status', 'in', [1, 2]]
     *     ['status', 'not in', [0]]
     *     // 范围查询
     *     ['created_at', 'between', ['2024-01-01', '2024-12-31']]
     *     ['created_at', 'not between', ['2023-01-01', '2023-12-31']]
     *     // null、not null 查询
     *     ['updated_at', 'null']
     *     ['deleted_at', 'not null']
     *     // FIND_IN_SET 查询
     *     ['tags', 'find_in_set', 'laravel'],
     *     // 原生 SQL 查询（raw）
     *     ['JSON_CONTAINS(tags, ?)', 'raw', ['"laravel"']]
     *     // OR 分组查询
     *     [
     *      ['or',
     *          ['status', '=', 1],
     *          ['status', '=', 2]
     *      ],
     *     ],
     *     // AND 分组查询
     *     ['and', ['name' => 'john'], ['status' => 1]]
     *     ['and', ['age', '>', 18], ['age', '<', 30]]
     *     // 嵌套 AND/OR 查询
     *     [
     *      [
     *      'or',
     *          ['and', ['name' => 'john'], ['age', '>', 18]],
     *          ['and', ['name' => 'tom'], ['age', '<', 30]]
     *      ],
     *     ]
     * ];
     */
    public function parseWhere(array $where): Builder
    {
        $builder = $this->model->newQuery();
        foreach ($where as $key => $value) {
            if (is_int($key) && is_array($value)) {
                // 处理 ThinkPHP 风格的二维数组条件
                $this->parseCondition($builder, $value);
            } elseif (!is_array($value)) {
                // 简单等值查询，如 ['name' => 'john']
                $builder->where($key, $value);
            }
        }

        return $builder;
    }

    /**
     * 解析单个 ThinkPHP 风格的查询条件（支持 and/or 嵌套）
     * @param Builder $query     查询对象
     * @param array   $condition 条件
     * @param string  $curMethod 当前逻辑，默认为 'where'
     * @return void
     */
    private function parseCondition(Builder $query, array $condition, string $curMethod = ''): void
    {
        // 忽略空条件
        if (empty($condition)) {
            return;
        }
        // 获取条件数量
        $conditionCount = count($condition);
        // 没有上级逻辑符则取现在的逻辑符
        if (!$curMethod) {
            $curMethod = 'where';
        }
        // 处理 and/or 分组
        if ($conditionCount >= 3) {
            // 处理 and/or 分组（必须是第一个元素为字符串 'and'/'or'）
            $logic = strtolower(reset($condition) ?? '');
            if (in_array($logic, ['and', 'or'])) {
                $method = $logic === 'and' ? 'where' : 'orWhere';
                // 递归处理子条件
                $query->$curMethod(function ($q) use ($condition, $method) {
                    for ($i = 1; $i < count($condition); $i++) {
                        $subCondition = $condition[$i];
                        if (is_array($subCondition)) {
                            $this->parseCondition($q, $subCondition, $method);
                        }
                    }
                });

                return;
            }
        }
        // 新增：处理 Laravel 风格的简单等值查询 ['name' => 'john']
        if ($conditionCount === 1 && is_string(key($condition))) {
            $column = key($condition);
            $value  = value($condition);
            $query->$curMethod($column, $value);

            return;
        }
        // 处理 is null
        if ($conditionCount === 2) {
            [$column, $value] = $condition;
            switch ($value) {
                case 'null':
                case 'is null':
                    if ($curMethod === 'orWhere') {
                        $query->orWhereNull($column);
                    } else {
                        $query->whereNull($column);
                    }
                    break;

                case 'not null':
                case 'is not null':
                    if ($curMethod === 'orWhere') {
                        $query->orWhereNotNull($column);
                    } else {
                        $query->whereNotNull($column);
                    }
                    break;
                default :
                    // 如果不是 null/not null，则按普通两元组处理，即隐式 '='
                    $query->$curMethod($column, $value);
            }

            return;
        }
        /**
         * 处理普通条件 三元组：['column', 'operator', 'value']
         * 这是最常见的条件格式，现在会根据 $currentMethod 决定是 where 还是 orWhere
         * 使用 array_pad 确保数组至少有三个元素，避免索引越界错误
         */
        [$column, $operator, $value] = array_pad($condition, 3, null);
        $operator = strtolower($operator);
        switch ($operator) {
            case '=':
            case '<>':
            case '>':
            case '>=':
            case '<':
            case 'not like':
            case 'like':
            case '<=':
                $query->$curMethod($column, $operator, $value);
                break;

            case 'in':
                if ($curMethod === 'orWhere') {
                    $query->orWhereIn($column, $value);
                } else {
                    $query->whereIn($column, $value);
                }
                break;

            case 'not in':
                if ($curMethod === 'orWhere') {
                    $query->orWhereNotIn($column, $value);
                } else {
                    $query->whereNotIn($column, $value);
                }
                break;

            case 'between':
                if (is_array($value) && count($value) === 2) {
                    if ($curMethod === 'orWhere') {
                        $query->orWhereBetween($column, $value);
                    } else {
                        $query->whereBetween($column, $value);
                    }
                }
                break;

            case 'not between':
                if (is_array($value) && count($value) === 2) {
                    if ($curMethod === 'orWhere') {
                        $query->orWhereNotBetween($column, $value);
                    } else {
                        $query->whereNotBetween($column, $value);
                    }
                }
                break;
            case 'find_in_set':
                $query->whereRaw("FIND_IN_SET(?, $column)", [(string)$value]);
                break;

            case 'raw':
                if ($curMethod === 'orWhere') {
                    $query->orWhereRaw($column, $value ?? []);
                } else {
                    $query->whereRaw($column, $value ?? []);
                }
                break;

            default:
                $query->$curMethod($column, '=', $operator);
                // 或者，如果你期望所有未匹配的操作符都抛出异常：
                // throw new \InvalidArgumentException("Unsupported operator: {$operator}");
                break;
        }
    }

    /**
     * 数据存在则抛异常
     * @param array $params
     * @param null $msg
     * @return void
     * @throws Exception
     */
    public function existsErr(array $params, $msg = null): void
    {
        if (static::isExists($params)) {
            throw new Exception($msg ?: 'data not found');
        }
    }

    /**
     * 数据不存在则抛异常
     * @param array $params
     * @param null $msg
     * @return void
     */
    public function notExistsErr(array $params, $msg = null): void
    {
        if (!$this->isExists($params)) {
            throw new Exception($msg ?: 'data not found');
        }
    }

    /**
     * 查询单个字段值
     * @param array  $where
     * @param string $column
     * @return mixed
     */
    public function column(mixed $where, string $column): mixed
    {
        return $this->parseWhere($where)->value($column);
    }

    /**
     * 数据是否存在
     * @param array $where
     * @return bool
     */
    public function isExists(array $where = []): bool
    {
        return $this->parseWhere($where)->exists();
    }

    /**
     * 单条条件查询
     * @param array  $where
     * @param array  $field
     * @param array  $relation
     * @param string $order
     * @param bool   $lock
     * @param bool   $trashed
     * @return Model|null
     */
    public function item(array $where, array $field = [], array $relation = [], string $order = '', bool $lock = false, bool $trashed = false): ?Model
    {
        $query = $this->parseWhere($where);
        if ($relation) {
            $query->with($relation);
        }
        if (!empty($order)) {
            $query->orderByRaw($order);
        }
        if (!empty($lock)) {
            $query->lockForUpdate();
        }
        if (!empty($trashed)) {
            $query->withTrashed();
        }

        return $query->select($field ?: $this->model->getFillable())->first();
    }

    /**
     * 多条条件查询
     * @param array      $where
     * @param array      $field
     * @param string     $order
     * @param int|string $limit
     * @param array      $relation
     * @return Collection
     */
    public function items(array $where = [], array $field = [], string $order = '', int|string $limit = 0, array $relation = []): Collection
    {
        $query = $this->parseWhere($where);
        if (!empty($order)) {
            $query->orderByRaw($order);
        }
        if (!empty($limit)) {
            $query->limit($limit);
        }
        if ($relation) {
            $query->with($relation);
        }

        return $query->get($field ?: $this->model->getFillable());
    }

    /**
     * 分页多条条件查询
     * @param array      $where
     * @param array      $field
     * @param int|string $page
     * @param int|string $pageSize
     * @param string     $order
     * @param array      $relation
     * @return LengthAwarePaginator
     */
    public function page(
        array $where = [],
        array $field = [],
        int|string $page = 1,
        int|string $pageSize = 15,
        string $order = '',
        array $relation = []
    ): LengthAwarePaginator {
        $query = $this->parseWhere($where);
        if (!empty($order)) {
            $query->orderByRaw($order);
        }
        if ($relation) {
            $query->with($relation);
        }

        return $query->paginate($pageSize, $field ?: $this->model->getFillable(), page: $page);
    }

    /**
     * 多条键值对
     * @param array  $where
     * @param string $column
     * @param string $key
     * @param string $order
     * @param array  $relation
     * @return Collection
     */
    public function pluckM(array $where, string $column, string $key = '', string $order = '', array $relation = []): Collection
    {
        $query = $this->parseWhere($where);
        if (!empty($order)) {
            $query->orderByRaw($order);
        }
        if ($relation) {
            $query->with($relation);
        }
        if (empty($key)) {
            $key = $this->model->getKeyName();
        }

        return $query->pluck($column, $key);
    }

    /**
     * 计数
     * @param array $where
     * @return int
     */
    public function count(array $where): int
    {
        return $this->parseWhere($where)->count();
    }
}
