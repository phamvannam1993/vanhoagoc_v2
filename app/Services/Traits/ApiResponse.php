<?php

// app/Traits/ApiResponse.php

namespace App\Services\Traits;

trait ApiResponse
{
    /**
     * Build a success response.
     *
     * @param mixed $data
     * @param string $message
     * @param string|null $messageCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse($data = null, $message = null)
    {
        return $this->apiResponse(true, $message, $data, null, 200);
    }

    /**
     * Build an error response.
     *
     * @param string $message
     * @param mixed $data
     * @param array $errors
     * @param int $statusCode
     * @param string|null $messageCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse($message = null, $errors = null, $statusCode = 400)
    {
        return $this->apiResponse(false, $message, null, $errors, $statusCode);
    }

    /**
     * Build a generic response.
     *
     * @param bool $status
     * @param string $message
     * @param mixed $data
     * @param array $errors
     * @param int $statusCode
     * @param string|null $messageCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function apiResponse($status, $message = null, $data = null, $errors = null, $statusCode = 200)
    {
        $status = $status ?? false;

        $response = [
            'status' => $status,
            'message' => $message,
            'data' => $data,
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $statusCode);
    }
}
