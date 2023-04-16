<?php

namespace App\Services;

use App\Constants\DirectoryConstant;
use App\Jobs\User\UploadUsersFromExcelFile;
use App\Repositories\RowRepository;
use Illuminate\Http\UploadedFile;

class UserService
{
    /**
     * @param UploadedFile $file
     * @return void
     */
    public function uploadUsersExcel(UploadedFile $file): void
    {
        $directory = storage_path(DirectoryConstant::USERS_EXCELS);
        $filename = $file->hashName();
        $file->move($directory, $filename);
        dispatch(new UploadUsersFromExcelFile($directory . '/' . $filename));
    }

    /**
     * @return array
     */
    public function getGroupByDate(): array
    {
        $rowRepository = new RowRepository();

        return $rowRepository->getGroupByDate();
    }
}
