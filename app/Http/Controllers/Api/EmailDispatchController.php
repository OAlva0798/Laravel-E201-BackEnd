<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

use App\Mail\UserCredentialsMail;
use App\Models\UserDetails;
use App\Models\UsersAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class EmailDispatchController extends Controller
{
    public function send(Request $request): JsonResponse
    {
        $validated = $request->validate([
            "target_type"         => ["required", "in:user"],
            "target_references"   => ["required", "array", "min:1", "max:500"],
            "target_references.*" => ["required", "string", "distinct"],
        ]);

        $users = UserDetails::whereIn("ud_uniqid", $validated["target_references"])
            ->whereNotNull("ud_email")
            ->get();

        if ($users->isEmpty()) {
            return response()->json(["message" => "No matching users with an email address were found."], 422);
        }

        $sent = 0;
        $failed = [];

        foreach ($users as $user) {
            $account = UsersAccount::where("uacc_email", $user->ud_email)->first();

            if (!$account) {
                $failed[] = $user->ud_email;
                continue;
            }

            $oldHash = $account->uacc_password;

            try {
                $name = trim(implode(" ", array_filter([
                    $user->ud_firstname, $user->ud_middlename, $user->ud_lastname,
                ]))) ?: $user->ud_email;

                // 1. Generate a new password and save its hash
                $plainPassword = Str::password(12);
                $account->uacc_password = Hash::make($plainPassword);
                $account->save();

                // 2. Email it (sent immediately, NOT queued, so the plaintext
                //    password is never stored in the jobs table)
                Mail::to($user->ud_email)->send(
                    new UserCredentialsMail($name, $user->ud_email, $plainPassword)
                );

                $sent++;
            } catch (\Throwable $e) {
                // Email failed: restore the old password so the user is not locked out
                $account->uacc_password = $oldHash;
                $account->save();

                Log::error("Email dispatch failed", ["user" => $user->ud_uniqid, "error" => $e->getMessage()]);
                $failed[] = $user->ud_email;
            }
        }

        if ($sent === 0) {
            return response()->json(["message" => "Unable to dispatch any emails.", "failed" => $failed], 500);
        }

        $message = "Email dispatched to {$sent} user" . ($sent === 1 ? "" : "s") . ".";
        if ($failed) {
            $message .= " " . count($failed) . " failed.";
        }

        return response()->json(["message" => $message, "sent" => $sent, "failed" => $failed]);
    }
}
