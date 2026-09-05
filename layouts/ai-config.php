<?php
/**
 * Gemini AI Agent configuration
 * Get a free API key: https://aistudio.google.com/apikey
 */
if (!defined('GEMINI_API_KEY')) {
    // Paste your free Gemini API key between the quotes below
    define('GEMINI_API_KEY', getenv('GEMINI_API_KEY') ?: 'AQ.Ab8RN6IROczJ601HVLvv8Zjy7ZiFHZQpMvkHVNt8zINLyB8EYg');
}

if (!defined('GEMINI_MODEL')) {
    // Free-tier friendly model
    define('GEMINI_MODEL', 'gemini-3.5-flash');
}

if (!defined('GEMINI_API_BASE')) {
    define('GEMINI_API_BASE', 'https://generativelanguage.googleapis.com/v1beta/models');
}
