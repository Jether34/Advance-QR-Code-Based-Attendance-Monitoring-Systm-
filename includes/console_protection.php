<!-- 
    JavaScript Production Wrapper
    This file provides a way to conditionally include console.log statements
    In production, console.log is disabled
-->
<script>
<?php if (defined('APP_ENV') && APP_ENV === 'production'): ?>
// Disable console in production for security
(function() {
    const noop = function() {};
    const methods = ['log', 'debug', 'info', 'warn', 'error', 'trace', 'dir', 'group', 'groupCollapsed', 'groupEnd', 'time', 'timeEnd', 'profile', 'profileEnd', 'dirxml', 'assert', 'count', 'markTimeline', 'timeStamp', 'clear'];
    const console = window.console = window.console || {};
    
    for (let i = 0; i < methods.length; i++) {
        console[methods[i]] = noop;
    }
})();
<?php else: ?>
// Development mode - console is enabled
console.log('%c🔧 Development Mode: Console logging enabled', 'color: #4CAF50; font-weight: bold; font-size: 14px;');
console.log('%cEnvironment: <?php echo APP_ENV ?? "development"; ?>', 'color: #2196F3;');
console.log('%cTo disable console in production, set APP_ENV=production in .env', 'color: #FF9800;');
<?php endif; ?>
</script>
