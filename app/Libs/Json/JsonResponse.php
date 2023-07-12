<?php

namespace App\Libs\Json;

/**
 * Wrapper for json response helper.
 * Contains common response
 */
class JsonResponse
{
    public static function success($data, $message = "Data berhasil diambil")
    {
        return response()->json([
            'status' => 'success',
            'data' => $data,
            'message' => $message
        ], 200);
    }

    public static function error($message = "Terjadi kesalahan")
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], 500);
    }

    public static function errorValidation($message)
    {
        return response()->json([
            'status' => 'warning',
            'message' => $message
        ], 422);
    }

    public static function badRequest($message)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], 400);
    }

    public static function notFound($message = "Data tidak ditemukan")
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], 404);
    }

    public static function unauthorized($message = "Unauthorized")
    {
        return response()->json([
            'status' => 'error',
            'message' => $message
        ], 401);
    }

}
