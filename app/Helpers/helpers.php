<?php

if (!function_exists('api_success')) {
    function api_success($data = null, string $message = 'OK', int $status = 200) {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data'    => $data,
        ], $status);
    }
}

if (!function_exists('api_error')) {
    function api_error(string $message, int $status = 400, $errors = null) {
        return response()->json([
            'success' => false,
            'message' => $message,
            'error'  => $errors,
        ], $status);
    }
}

if (!function_exists('user_avatar_url')) {
    function user_avatar_url($user) {
        if (!$user || !$user->avatar_url) {
            return null;
        }
        return url('/api/user/avatar/' . $user->id);
    }
}
