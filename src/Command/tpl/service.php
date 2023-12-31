declare (strict_types=1);

namespace App\Service\<?= $nameSpace ?>;

use App\Data\<?= $nameSpace ?>\<?= $modelName ?>Data;
use App\Service\Base\BaseService;
use App\Struct\<?= $nameSpace ?>\<?= $modelName ?>Struct;
use App\Utils\Tool;
use Exception;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Collection\Arr;

class <?= $modelName ?>Service extends BaseService
{
    #[Inject]
    protected <?= $modelName ?>Data $<?= $modelNameCamel ?>Data;

    /**
     * <?= $tableDesc ?> 检测
     * @param <?= $modelName ?>Struct $struct
     * @return void
     */
    private function commonCheck(<?= $modelName ?>Struct $struct): void
    {
        
    }

    /**
     * <?= $tableDesc ?> 添加关联数据
     * @param <?= $modelName ?>Struct $struct
     * @return void
     */
    private function addRelation(<?= $modelName ?>Struct $struct): void
    {
        
    }

    /**
     * <?= $tableDesc ?> 操作
     * @param <?= $modelName ?>Struct $struct
     * @return array
     */
    public function operate(<?= $modelName ?>Struct $struct): array
    {
        // 判断是否有主键，选择处理逻辑
        if ($struct->has<?= $pkCamel ?>()) {
            return $this->update($struct);
        } else {
            return $this->create($struct);
        }
    }

    /**
     * <?= $tableDesc ?> 添加
     * @param <?= $modelName ?>Struct $struct
     * @return array
     */
    public function create(<?= $modelName ?>Struct $struct): array
    {
        // 检测
        $this->commonCheck($struct);
        $generateId = $this->snowflake->generate();
        $struct->set<?= $pkCamel ?>($generateId);
        $struct->setCreatedAt(date('Y-m-d H:i:s'));

        try {
            Db::beginTransaction();
            $one = $this-><?= $modelNameCamel ?>Data->insertOne($struct->getAll());
            // 添加关联数据
            $this->addRelation($struct);
            Db::commit();
            return $one;
        } catch (Exception $e) {
            Db::rollBack();
            Tool::errorTrace($e);
        }
    }

    /**
     * <?= $tableDesc ?> 删除
     * @param <?= $modelName ?>Struct $struct
     * @return void
     */
    public function delete(<?= $modelName ?>Struct $struct): void
    {
        $this-><?= $modelNameCamel ?>Data->notExistsErr(['<?= $pk ?>' => $struct->get<?= $pkCamel ?>()]);
        try {
            Db::beginTransaction();
            $this-><?= $modelNameCamel ?>Data->deleteByPK($struct->get<?= $pkCamel ?>());
            Db::commit();
        } catch (Exception $e) {
            Db::rollBack();
            Tool::errorTrace($e);
        }
    }

    /**
     * <?= $tableDesc ?> 修改
     * @param <?= $modelName ?>Struct $struct
     * @return array
     */
    public function update(<?= $modelName ?>Struct $struct): array
    {
        $params = $struct->getAll();
        $one    = $this-><?= $modelNameCamel ?>Data->findOneOrFail($struct->get<?= $pkCamel ?>());
        // 检测
        $this->commonCheck($struct);
        try {
            Db::beginTransaction();
            // 更新
            $one->fill(Arr::except($params, [$one->getCreatedAtColumn()]))->save();
            // 添加关联数据
            $this->addRelation($struct);
            Db::commit();

            return $one->toArray();
        } catch (Exception $e) {
            Db::rollBack();
            Tool::errorTrace($e);
        }
    }

    /**
     * <?= $tableDesc ?> 列表
     * @param <?= $modelName ?>Struct $struct
     * @return array
     */
    public function list(<?= $modelName ?>Struct $struct): array
    {
        return $this-><?= $modelNameCamel ?>Data->pageData(['page_size' => $struct->getAttach('page_size')]);
    }

    /**
     * <?= $tableDesc ?> 详情
     * @param <?= $modelName ?>Struct $struct
     * @return array
     */
    public function detail(<?= $modelName ?>Struct $struct): array
    {
        return $this-><?= $modelNameCamel ?>Data->item(['<?= $pk ?>' => $struct->get<?= $pkCamel ?>()]);
    }
}