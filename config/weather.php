<?php
return [
    'key' => env('OPENWEATHER_API_KEY', ''),
    'label' => env('WEATHER_LABEL', 'Jakarta Barat'),
    // Titik perkiraan wilayah Cengkareng, bukan koordinat presisi gedung lab.
    'latitude' => env('WEATHER_LAT', '-6.149344'),
    'longitude' => env('WEATHER_LON', '106.721920'),
    'city' => env('WEATHER_CITY', 'Jakarta,ID'),
];
