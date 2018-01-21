<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\User;
use App\Notification as Message;
use App\Notifications\PrivateMessage;
use App\Http\Resources\PrivateMessageResource;
use App\Http\Resources\PrivateMessages;
use App\Http\Requests\ShowMessage;
use App\Http\Resources\PrivateMessageCollection;
use Illuminate\Support\Facades\Auth;
use Validator;


class MessageController extends Controller
{
    public function index(Request $request)
    {
        return new PrivateMessageCollection( Message::where('type', PrivateMessage::class)->paginate());
    }

    public function store(Request $request)
    {
        Auth::user()->notify(new PrivateMessage((object)['id' => $request->to, 'data' => $request->data]));
    }

    public function show(ShowMessage $request, $id)
    {
        return new PrivateMessageResource(Message::find($id));
    }

    public function destroy(Request $request, $id)
    {
        $user = Auth::user();
        $user->notifications()->where('type', PrivateMessage::class)
            ->where('notifiable_id', $user->id)
            ->where('id', $id)
            ->first()
            ->delete();
    }

    public function messages(Request $request)
    {
        return new PrivateMessageCollection( Auth::user()->notifications()->where('type', PrivateMessage::class)->paginate());
    }

    public function read(Request $request, $id)
    {

    }


}