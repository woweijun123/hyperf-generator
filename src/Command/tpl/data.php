declare (strict_types=1);

namespace App\Data\<?= $nameSpace ?>;

use <?= $dBase ?>;
use App\Model\<?= $nameSpace ?>\<?= $modelName ?>Model;
use Hyperf\Database\Model\Builder;
use Hyperf\Di\Annotation\Inject;

class <?= $modelName ?>Data extends <?= $dBaseName ?>

{
    #[Inject]
    public <?= $modelName ?>Model $model;
}
