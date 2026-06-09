<?php

namespace App\Http\Controllers;

use App\Enums\StatusCode;
use App\Helpers\ResponseCode;
use Illuminate\Http\Response;

class BaseModuleController extends Controller
{
    public function successResponse($data = null)
    {
        return response()->json([
            'status' => true,
            'message' => 'Thành công!',
            'data' => $data
        ], Response::HTTP_OK);
    }

    public function createdSuccessResponse()
    {
        return response()->json([
            'message' => ResponseCode::$CREATED,
        ], Response::HTTP_CREATED);
    }

    public function uploadImageSuccessResponse($imageUrl)
    {
        return response()->json([
            'url' => $imageUrl,
        ], Response::HTTP_CREATED);
    }

    public function updatedSuccessResponse()
    {
        return response()->json([
            'message' => ResponseCode::$UPDATED,
        ], Response::HTTP_OK);
    }

    public function delectedSuccessResponse()
    {
        return response()->json([
            'result' => true,
            'message' => ResponseCode::$DELETED,
        ], Response::HTTP_OK);
    }

    public function badRequestErrorResponse()
    {
        return response()->json([
            'errorCode' => StatusCode::BAD_REQUEST,
            'message' => ResponseCode::$BAD_REQUEST
        ], Response::HTTP_BAD_REQUEST);
    }

    public function unAuthorizedErrorResponse()
    {
        return response()->json([
            'errorCode' => StatusCode::UNAUTHORIZED,
            'message' => ResponseCode::$UNAUTHORIZED
        ], Response::HTTP_UNAUTHORIZED);
    }

    public function accessDeniedErrorResponse()
    {
        return response()->json([
            'errorCode' => StatusCode::FORBIDDEN,
            'message' => ResponseCode::$ACCESS_DENIED
        ], Response::HTTP_FORBIDDEN);
    }

    public function notFoundErrorResponse()
    {
        return response()->json([
            'errorCode' => StatusCode::NOT_FOUND,
            'message' => ResponseCode::$NOT_FOUND
        ], Response::HTTP_NOT_FOUND);
    }

    public function internalServerErrorResponse($message = '')
    {
        return response()->json([
            'status' => false,
            'message' => $message ?: ResponseCode::$INTERNAL_ERROR
        ], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    public function notFoundErrorWithCustomResponse($errorCode, $message = '')
    {
        return response()->json([
            'errorCode' => $errorCode,
            'message' => $message ?: ResponseCode::$NOT_FOUND
        ], Response::HTTP_NOT_FOUND);
    }

    public function validateErrorResponse($data)
    {
        return response()->json([
            'status' => false,
            'message' => $data
        ], Response::HTTP_BAD_REQUEST);
    }

    public function customErrorResponse($errorCode, $message, $httpErrorCode)
    {
        return response()->json([
            'errorCode' => $errorCode,
            'message' => $message,
        ], $httpErrorCode);
    }
}
