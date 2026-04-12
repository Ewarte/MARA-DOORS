<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Illuminate\Support\Collection;

class UniversalExport implements FromCollection, WithHeadings, WithMapping
{
    protected $collection;
    protected $headings;
    protected $mapping;

    public function __construct(Collection $collection, array $headings, array $mapping)
    {
        $this->collection = $collection;
        $this->headings = $headings;
        $this->mapping = $mapping;
    }

    public function collection()
    {
        return $this->collection;
    }

    public function headings(): array
    {
        return $this->headings;
    }

    public function map($item): array
    {
        $row = [];
        foreach ($this->mapping as $field) {
            if (is_callable($field)) {
                $row[] = $field($item);
            } else {
                $row[] = data_get($item, $field) ?? 'N/A';
            }
        }
        return $row;
    }
}
