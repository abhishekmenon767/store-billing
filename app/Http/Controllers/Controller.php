<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

abstract class Controller
{
    protected function successResponse(mixed $data = null, string $message = 'Success', int $status = 200, array $meta = []): JsonResponse
    {
        $body = ['success' => true, 'message' => $message];

        if ($data instanceof JsonResource) {
            $resolved = $data->response()->getData(true);
            $data = $resolved['data'];
            $meta = array_merge($resolved['meta'] ?? [], $meta);

            if (isset($resolved['links'])) {
                $body['links'] = $resolved['links'];
            }
        }

        $body['data'] = $data;

        if ($meta) {
            $body['meta'] = $meta;
        }

        return response()->json($body, $status);
    }

    protected function createdResponse(mixed $data = null, string $message = 'Created successfully'): JsonResponse
    {
        return $this->successResponse($data, $message, 201);
    }

    protected function errorResponse(string $message, int $status = 400, array $errors = []): JsonResponse
    {
        return response()->json(array_filter([
            'success' => false,
            'message' => $message,
            'errors' => $errors ?: null,
        ], fn ($value) => $value !== null), $status);
    }

    protected function pdfResponse(string $content, string $filename): Response
    {
        return response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => "inline; filename=\"{$filename}\"",
        ]);
    }
}
