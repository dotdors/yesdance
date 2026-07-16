<?php
/**
 * Plugin Name: Remove EPC Purge Cron
 */

add_action('init', function() {
    $crons = _get_cron_array();
    if (is_array($crons)) {
        foreach ($crons as $timestamp => $hooks) {
            if (isset($hooks['epc_purge_request'])) {
                unset($crons[$timestamp]['epc_purge_request']);
                if (empty($crons[$timestamp])) {
                    unset($crons[$timestamp]); // clean empty timestamp
                }
            }
        }
        _set_cron_array($crons);
    }
});
