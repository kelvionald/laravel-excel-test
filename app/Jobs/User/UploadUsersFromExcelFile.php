<?php

namespace App\Jobs\User;

use App\Models\Row;
use App\Repositories\RowRepository;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Validator;
use PhpOffice\PhpSpreadsheet\Reader\Exception;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;

class UploadUsersFromExcelFile implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private const CHUNK_SIZE = 1000;

    private RowRepository $rowRepository;
    private string $path;

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $this->path = $path;
    }

    /**
     * @param RowRepository $rowRepository
     * @return void
     * @throws Exception
     */
    public function handle(RowRepository $rowRepository): void
    {
        $this->rowRepository = $rowRepository;
        $chunks = $this->getChunkedRowsFromExcelFile($this->path, self::CHUNK_SIZE);

        foreach ($chunks as $chunk) {
            $this->saveRows($chunk);
        }
    }

    /**
     * @param string $path
     * @param int $chunkSize
     * @return array
     * @throws Exception
     */
    private function getChunkedRowsFromExcelFile(string $path, int $chunkSize): array
    {
        $reader = new Xlsx();
        $spreadsheet = $reader->load($path);
        $sheetData = $spreadsheet->getActiveSheet()->toArray();
        unset($sheetData[0]);

        return array_chunk($sheetData, $chunkSize);
    }

    /**
     * @param array $chunk
     * @return void
     */
    private function saveRows(array $chunk): void
    {
        $preparedKey = 'users_file:prepared:' . basename($this->path);

        DB::beginTransaction();

        foreach ($chunk as $row) {
            if (count($row) < 3) {
                Log::warning('Пустые колонки в файле.', ['path' => $this->path]);

                break;
            }

            [$id, $name, $date] = $row;

            $validator = Validator::make(compact('id', 'name'), [
                'id' => 'integer',
                'name' => 'max:255',
            ]);

            if ($validator->fails()) {
                Log::warning('Ошибка валидации данных.', ['errors' => $validator->errors()->toArray()]);

                break;
            }

            $date = Carbon::createFromFormat('d.m.y', $date);

            $row = $this->rowRepository->find($id);

            if (!$row) {
                $row = new Row();
                $row->id = $id;
            }

            $row->name = $name;
            $row->date = $date;
            $this->rowRepository->save($row);

            Redis::incr($preparedKey);
        }

        DB::commit();
    }
}
