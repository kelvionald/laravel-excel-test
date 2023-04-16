<?php

namespace App\Repositories;

use App\Models\Row;
use Illuminate\Support\Facades\DB;

class RowRepository
{
    /**
     * @return array
     */
    public function getGroupByDate(): array
    {
        return Row::query()
            ->select('date', DB::raw('count(*) as total'))
            ->groupBy('date')
            ->get()
            ->toArray();
    }

    /**
     * @param int $id
     * @return Row|null
     */
    public function find(int $id): ?Row
    {
        return Row::query()->find($id);
    }

    /**
     * @param Row $row
     * @return void
     */
    public function save(Row $row): void
    {
        $row->save();
    }
}
