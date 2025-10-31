<?php

use App\Http\Controllers\JobPostController;
use App\Http\Controllers\ModeratorNotificationController;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Http;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/job-post', [JobPostController::class, 'index']);
Route::get('/job-post/{jobPost}', [JobPostController::class, 'show']);
Route::get('/external-job-post/{id}', [JobPostController::class, 'getExternalData']);
Route::post('/job-post', [JobPostController::class, 'store']);
Route::patch('/job-post/{jobPost}', [JobPostController::class, 'update']);
Route::get('/moderator-notifications', [ModeratorNotificationController::class, 'index']);
Route::get('/ext-job-post', function() {
    try {
        $response = Http::timeout(10)->get('https://mrge-group-gmbh.jobs.personio.de/xml');

        if ($response->successful()) {
            $xml = simplexml_load_string($response->body(), 'SimpleXMLElement', LIBXML_NOCDATA);
            $json = json_encode($xml);
            $data = json_decode($json, true);
        }

        return response()->json([
            'status' => Response::HTTP_OK,
            'data' => $data,
            'message' => 'Successfully fetched the external data.'
        ]);
    } catch (\Throwable $th) {
        throw $th;
        return response()->json([
            'status' => Response::HTTP_BAD_REQUEST,
            'data' => [],
            'message' => 'Failed to fetched the external data.'
        ]);
    }
});
