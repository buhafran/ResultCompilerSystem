<?php
return [
    'gemini' => ['key' => env('GEMINI_API_KEY'), 'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'), 'enabled' => env('AI_COMMENTS_ENABLED', true)],
];
