declare (strict_types=1);

namespace App\Validator\<?= $nameSpace ?>;

use <?=$vBase?>;

class <?=$modelName?>Validator extends <?=$vBaseName?>

{
    /**
     * @param $data
     * @return array
     */
    public function create($data): array
    {
        return $this->verify($data, $this->baseRule(), $this->baseMsg());
    }

    /**
     * @param $data
     * @return array
     */
    public function delete($data): array
    {
        return $this->verify($data, $this->idRule());
    }

    /**
     * @param $data
     * @return array
     */
    public function update($data): array
    {
        $rule = array_merge($this->baseRule(), $this->idRule());
        $msg  = array_merge($this->baseMsg(), $this->idMsg());

        return $this->verify($data, $rule, $msg);
    }

    /**
     * @param $data
     * @return array
     */
    public function detail($data): array
    {
        $rule = ['id' => ['required', 'string', 'min:0']];

        return $this->verify($data, $rule);
    }

    /**
     * @param array $params
     * @return array
     */
    public function list(array $params): array
    {
        $rule = [
            'page'      => ['required', 'integer', 'min:1'],
            'page_size' => ['required', 'integer', 'min:15'],
        ];
        $msg  = [
            'page.integer'      => 'page 必须为整型',
            'page.min'          => 'page 最小为:min',
            'page_size.integer' => 'page_size 必须为整型',
            'page_size.min'     => 'page_size 最小为:min',
        ];

        return $this->verify($params, $rule, $msg);
    }

    /**
     * @return string[][]
     */
    private function baseRule(): array
    {
        return [];
    }

    /**
     * @return string[][]
     */
    private function idRule(): array
    {
        return ['id' => ['required', 'integer', 'min:0']];
    }

    /**
     * @return string[][]
     */
    private function idMsg(): array
    {
        return ['iid.required' => 'ID 必填'];
    }

    /**
     * @return string[]
     */
    private function baseMsg(): array
    {
        return [];
    }
}
