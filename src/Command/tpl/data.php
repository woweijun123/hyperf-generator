declare (strict_types=1);

namespace App\Data\<?= $nameSpace ?>;

use App\Data\Base\DataTrait;
use App\Model\<?= $nameSpace ?>\<?= $modelName ?>Model;
use Hyperf\Di\Annotation\Inject;

class <?= $modelName ?>Data
{
    use DataTrait;

    #[Inject]
    public <?= $modelName ?>Model $model;
}