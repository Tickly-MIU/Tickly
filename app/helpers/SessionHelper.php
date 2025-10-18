<?php
// Session helper: convenience wrappers around $_SESSION.
class SessionHelper {
    public static function set($k, $v) { $_SESSION[$k] = $v; }
    public static function get($k, $default = null) { return $_SESSION[$k] ?? $default; }
    public static function destroy() { session_destroy(); }
}
