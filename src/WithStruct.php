<?php
namespace Riven;

use Riven\Exceptions\InvalidStructClass;

/**
 * @template BaseStruct
 */
trait WithStruct
{
    /**
     * @return BaseStruct
     * @throws InvalidStructClass
     */
    public function getStruct(): BaseStruct
    {
        $structClass = match (true) {
            /** @psalm-suppress UndefinedThisPropertyFetch */
            property_exists($this, 'structClass') => $this->structClass,
            method_exists($this, 'structClass') => $this->structClass(),
            default => null,
        };

        if (!is_a($structClass, BaseStruct::class, true)) {
            throw InvalidStructClass::create($structClass);
        }

        return $structClass::make($this->validated());
    }
}
