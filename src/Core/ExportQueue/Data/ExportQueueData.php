<?php

namespace EffectConnect\Marketplaces\Core\ExportQueue\Data;

abstract class ExportQueueData
{
    public static abstract function fromArray(array $data);
    public abstract function toArray(): array;
}