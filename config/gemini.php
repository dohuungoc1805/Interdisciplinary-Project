<?php

return [
    'api_key' => env('GEMINI_API_KEY', ''),
    'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
    'chat_max_candidates' => (int) env('GEMINI_CHAT_MAX_CANDIDATES', 6),
    'chat_temperature' => (float) env('GEMINI_CHAT_TEMPERATURE', 0.35),
];
