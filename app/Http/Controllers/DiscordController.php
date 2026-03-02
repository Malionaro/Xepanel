<?php

namespace App\Http\Controllers;

use App\Models\DiscordMessage;
use Illuminate\Http\Request;

class DiscordController extends Controller
{
    public function webhook(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string',
            'content' => 'required|string',
            'avatar_url' => 'nullable|url',
        ]);

        DiscordMessage::add($data);

        return response()->json(['status' => 'success']);
    }

    public function getMessages()
    {
        return response()->json(DiscordMessage::all());
    }
}
