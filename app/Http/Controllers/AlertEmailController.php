<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\AlertEmail;

class AlertEmailController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $user = Auth::user();

        $existingEmail = AlertEmail::where('user_id', $user->id)
            ->where('email', $request->email)
            ->first();

        if ($existingEmail) {
            return response()->json(['message' => 'Email already exists'], 409);
        }

        $alertEmail = new AlertEmail();
        $alertEmail->user_id = $user->id;
        $alertEmail->email = $request->email;
        $alertEmail->save();

        return response()->json(['message' => 'Email added successfully'], 201);
    }
    public function destroy($id)
    {
        $user = Auth::user();
        $alertEmail = AlertEmail::where('user_id', $user->id)->findOrFail($id);
        $alertEmail->delete();

        return response()->json(['message' => 'Email deleted successfully'], 200);
    }
    public function index()
    {
        $user = Auth::user();
        $alertEmails = AlertEmail::where('user_id', $user->id)->get();

        return response()->json($alertEmails, 200);
    }
    public function show($id)
    {
        $user = Auth::user();
        $alertEmail = AlertEmail::where('user_id', $user->id)->findOrFail($id);

        return response()->json($alertEmail, 200);
    }
}
