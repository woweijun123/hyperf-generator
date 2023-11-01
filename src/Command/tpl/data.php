declare (strict_types=1);

namespace App\Data\<?= $nameSpace ?>;

use App\Data\Base\DataTrait;
use App\Model\<?= $nameSpace ?>\<?= $modelName ?>Model;
use Hyperf\Database\Model\Builder;
use Hyperf\Di\Annotation\Inject;

class <?= $modelName ?>Data
{
    use DataTrait;

    #[Inject]
    public <?= $modelName ?>Model $model;

    /**
    * @param array $condition
    * @return Builder
    */
    public function commonQuery(array $condition): Builder
    {
        $builder = $this->model->newQuery();
        $pk      = $this->model->getKeyName();
        if (!empty($condition[$pk])) {
            $builder->where($pk, $condition[$pk]);
        }

        return $builder;
    }
}
