<?php

function yamu_driver_service_locations()
{
    return [
        'Colombo',
        'Gampaha',
        'Kalutara',
        'Kandy',
        'Matale',
        'Nuwara Eliya',
        'Galle',
        'Matara',
        'Hambantota',
        'Jaffna',
        'Kilinochchi',
        'Mannar',
        'Mullaitivu',
        'Vavuniya',
        'Eastern Province',
        'Trincomalee',
        'Batticaloa',
        'Ampara',
        'Kurunegala',
        'Puttalam',
        'Anuradhapura',
        'Polonnaruwa',
        'Uva Province',
        'Badulla',
        'Monaragala',
        'Ratnapura',
        'Kegalle',
    ];
}

function yamu_driver_service_location_exists($location)
{
    return in_array(trim((string) $location), yamu_driver_service_locations(), true);
}

function yamu_driver_language_options()
{
    return [
        'English',
        'Sinhala',
        'Tamil',
        'English, Sinhala',
        'English, Tamil',
        'Sinhala, Tamil',
        'English, Sinhala, Tamil',
    ];
}

function yamu_driver_language_exists($language)
{
    return in_array(trim((string) $language), yamu_driver_language_options(), true);
}
