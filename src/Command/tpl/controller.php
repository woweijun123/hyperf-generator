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
        $params = $this->request->all();
        $this->validator->create($params);
        return $this->service->operate(<?= $modelName ?>Struct::make($params));
    }

    /**
     * <?= $tableDesc ?> 删除
     * @return array
     */
    public function delete(): array
    {
        $params = $this->request->all();
        $this->validator->delete($params);
        $this->service->delete(<?= $modelName ?>Struct::make($params));

        return Tool::success();
    }

    /**
     * <?= $tableDesc ?> 修改
     * @return array
     */
    public function update(): array
    {
        $params = $this->request->all();
        $this->validator->update($params);
        $data = $this->service->update(<?= $modelName ?>Struct::make($params));

        return Tool::success($data);
    }

    /**
     * <?= $tableDesc ?> 列表
     * @return array
     */
    public function list(): array
    {
        $params = $this->request->all();
        $this->validator->list($params);

        return Tool::success($this->service->list(<?= $modelName ?>Struct::make($params)));
    }

    /**
     * <?= $tableDesc ?> 详情
     * @return array
     */
    public function detail(): array
    {
        $params = $this->request->all();
        $this->validator->detail($params);

        return Tool::success($this->service->detail(<?= $modelName ?>Struct::make($params)));
    }
}