<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ModeratorNotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $notifications = User::find(1)->unreadNotifications;
        $notificationsArray = $notifications->map(fn($n) => $n->toArray())->values()->all();
        // $notifications->markAsRead();

        return response()->json([
            'status' => Response::HTTP_OK,
            'data' => $notificationsArray,
            'message' => 'Successfully fetched data.'
        ]);
    }
}
