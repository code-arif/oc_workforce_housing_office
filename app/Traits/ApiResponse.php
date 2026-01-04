<?php

namespace App\Traits;

trait ApiResponse
{
    public function success($data, $message = null, $code = 200)
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
            'code' => $code,

        ], $code);
    }


    public function error($data, $message = null, $code = 500)
    {
        return response()->json([
            'status' => false,
            'message' => $message,
            'data' => $data,
            'code' => $code
        ], $code);
    }

    /*
    * Validation error
    */
    public function validationError($errors, $message = 'Validation failed', $code = 422)
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data'    => [],
            'errors'  => $errors,
            'code'    => $code
        ], $code);
    }
}
