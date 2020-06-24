<?php

namespace Vemcogroup\Weather\Objects;

class DataBlock
{
    private $summary;
    private $icon;
    private $data;

    public function __construct($data)
    {
        if (isset($data['summary'])) {
            $this->summary = $data['summary'];
        }
        if (isset($data['icon'])) {
            $this->icon = $data['icon'];
        }
        if (isset($data['data'])) {
            foreach ($data['data'] as $dataPoint) {
                $this->data[] = new DataPoint($dataPoint);
            }
        }
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function getData(): array
    {
        return $this->data;
    }
}
