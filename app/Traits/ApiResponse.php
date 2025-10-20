<?php

namespace App\Traits;

trait ApiResponse
{
    protected function successResponse($data, $message = "success" , $status = 200) : \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'status' => $status
        ],$status);
    }

    protected function errorResponse(string $message, $status = 400 , $errors = null): \Illuminate\Http\JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
            'status' => $status
        ], $status);
    }
}
