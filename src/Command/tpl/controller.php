declare (strict_types=1);

namespace App\Controller\<?= $nameSpace ?>;

use App\Controller\AbstractController;
use App\Utils\Tool;
use Hyperf\Di\Annotation\Inject;
use App\Service\<?= $nameSpace ?>\<?= $modelName ?>Service;
use App\Struct\<?= $nameSpace ?>\<?= $modelName ?>Struct;
use App\Validator\<?= $nameSpace ?>\<?= $modelName ?>Validator;

class <?= $modelName ?>Controller extends AbstractController
{
    #[Inject]
    public <?= $modelName ?>Service $service;

    #[Inject]
    public <?= $modelName ?>Validator $validator;

    /**
     * <?= $tableDesc ?> 新增
     * @return array
     */
    public function create(): array
    {
        $params = $this->getParams();
        $this->validator->create($params);
        return $this->service->operate(Tool::getMappingStruct(<?= $modelName ?>Struct::class, $params));
    }

    /**
     * <?= $tableDesc ?> 删除
     * @return array
     */
    public function delete(): array
    {
        $params = $this->getParams();
        $this->validator->delete($params);
        $this->service->delete(Tool::getMappingStruct(<?= $modelName ?>Struct::class, $params));

        return Tool::success();
    }

    /**
     * <?= $tableDesc ?> 修改
     * @return array
     */
    public function update(): array
    {
        $params = $this->getParams();
        $this->validator->update($params);
        $data = $this->service->update(Tool::getMappingStruct(<?= $modelName ?>Struct::class, $params));

        return Tool::success($data);
    }

    /**
     * <?= $tableDesc ?> 列表
     * @return array
     */
    public function list(): array
    {
        $params = $this->getParams();
        $this->validator->list($params);

        return Tool::success($this->service->list(Tool::getMappingStruct(<?= $modelName ?>Struct::class, $params)));
    }

    /**
     * <?= $tableDesc ?> 详情
     * @return array
     */
    public function detail(): array
    {
        $params = $this->getParams();
        $this->validator->detail($params);

        return Tool::success($this->service->detail(Tool::getMappingStruct(<?= $modelName ?>Struct::class, $params)));
    }
}