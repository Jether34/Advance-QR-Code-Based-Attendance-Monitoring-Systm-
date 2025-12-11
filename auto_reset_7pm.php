<?php
// auto_reset_7pm.php - Automatic daily reset at 7PM Manila time
// This script checks if it's past 7PM and adjusts the "current date" for attendance tracking

date_default_timezone_set('Asia/Manila');

/**
 * Get the effective attendance date based on current time
 * - Before 7PM: Returns today's date
 * - After 7PM: Returns tomorrow's date (preparing for next day)
 *
 * This ensures teachers can prepare for the next day after 7PM
 */
function get_attendance_date() {
    $current_hour = (int)date('H');

    // After 7PM (19:00), switch to next day
    if ($current_hour >= 19) {
        return date('Y-m-d', strtotime('+1 day'));
    }

    return date('Y-m-d');
}

/**
 * Get the display date for dashboard
 * Shows which day the system is currently tracking
 */
function get_dashboard_display_date() {
    $attendance_date = get_attendance_date();
    $current_hour = (int)date('H');

    if ($current_hour >= 19) {
        return date('F j, Y', strtotime($attendance_date)) . ' (Next Day - Ready at 6AM)';
    }

    return date('F j, Y', strtotime($attendance_date)) . ' (Today)';
}

/**
 * Check if we're in "next day preparation mode" (after 7PM)
 */
function is_next_day_mode() {
    return (int)date('H') >= 19;
}

/**
 * Get time until next reset (7PM)
 */
function get_time_until_reset() {
    $current_time = time();
    $today_7pm = strtotime('today 19:00:00');

    if ($current_time >= $today_7pm) {
        // Already past 7PM, show time until tomorrow's 7PM
        $next_7pm = strtotime('tomorrow 19:00:00');
    } else {
        // Before 7PM, show time until today's 7PM
        $next_7pm = $today_7pm;
    }

    $seconds_remaining = $next_7pm - $current_time;
    $hours = floor($seconds_remaining / 3600);
    $minutes = floor(($seconds_remaining % 3600) / 60);

    return [
        'hours' => $hours,
        'minutes' => $minutes,
        'seconds' => $seconds_remaining,
        'next_reset_time' => date('h:i A', $next_7pm),
        'is_next_day_mode' => is_next_day_mode()
    ];
}

/**
 * Get attendance status message for display
 */
function get_attendance_status_message() {
    $current_hour = (int)date('H');

    if ($current_hour >= 6 && $current_hour < 19) {
        return [
            'status' => 'active',
            'message' => 'Attendance tracking is ACTIVE',
            'class' => 'success'
        ];
    } elseif ($current_hour >= 19 || $current_hour < 6) {
        $next_date = get_attendance_date();
        return [
            'status' => 'preparing',
            'message' => 'Preparing for ' . date('F j, Y', strtotime($next_date)) . ' (Ready at 6AM)',
            'class' => 'warning'
        ];
    }
}
?>
