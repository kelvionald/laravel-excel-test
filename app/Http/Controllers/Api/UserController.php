<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\User\UserExcelUploadRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use function response;

class UserController extends Controller
{
    /**
     * @param UserExcelUploadRequest $request
     * @param UserService $userService
     * @return JsonResponse
     */
    public function uploadFile(UserExcelUploadRequest $request, UserService $userService): JsonResponse
    {
        $file = $request->file('users');
        $userService->uploadUsersExcel($file);

        return response()->json();
    }

    /**
     * @param UserService $userService
     * @return JsonResponse
     */
    public function getGroupByDate(UserService $userService): JsonResponse
    {
        return response()->json($userService->getGroupByDate());
    }
}
