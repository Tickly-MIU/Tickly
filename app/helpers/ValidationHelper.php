<?php
// Small validation utils for input sanitization.
class ValidationHelper {
    public static function sanitize($data) {
        return htmlspecialchars(trim($data));
    }
}
