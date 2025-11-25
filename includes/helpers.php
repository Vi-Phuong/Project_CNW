<?php
/**
 * Helper Functions
 * Sanitize, format, utility functions
 */

/**
 * Sanitize input: trim, escape HTML
 */
function sanitize($input) {
    return htmlspecialchars(trim($input ?? ''), ENT_QUOTES, 'UTF-8');
}

/**
 * Format date to Vietnamese format (dd/mm/yyyy hh:mm)
 */
function formatDate($date) {
    if (!$date) return '-';
    try {
        return date('d/m/Y H:i', strtotime($date));
    } catch (Exception $e) {
        return '-';
    }
}

/**
 * Format date to short format (dd/mm/yyyy)
 */
function formatDateShort($date) {
    if (!$date) return '-';
    try {
        return date('d/m/Y', strtotime($date));
    } catch (Exception $e) {
        return '-';
    }
}

/**
 * Redirect with optional message
 */
function redirect($url, $message = null) {
    if ($message) {
        $_SESSION['message'] = $message;
    }
    header('Location: ' . $url);
    exit;
}

/**
 * Get session message and clear
 */
function getSessionMessage() {
    if (isset($_SESSION['message'])) {
        $msg = $_SESSION['message'];
        unset($_SESSION['message']);
        return $msg;
    }
    return null;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_email']);
}

/**
 * Get current user ID from session
 */
function getCurrentUserId() {
    return $_SESSION['user_id'] ?? null;
}

?>
