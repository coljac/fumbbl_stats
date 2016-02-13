<?php
/**
 * Plugin Name: Fumbbl Stats for Wordpress
 * Plugin URI: http://github.io/coljac/fumbbl_stats
 * Description: Pulls in and displays statistics from your Fumbbl league (fumbbl.com)
 * Version: 1.0.0
 * Author: Colin Jacobs
 * Author URI: http://coljac.net
 * License: GPL2
 */


add_action('admin_menu', 'fumbbl_plugin_menu');

function fumbbl_plugin_menu() {
        add_menu_page('Fumbbl Plugin Settings', 'Fumbbl Settings', 'administrator', 'fumbbl-stats-settings', 'fumbbl_stats_settings_page', 'dashicons-admin-generic');
}

function fumbbl_stats_settings_page() {
?>
<div class="wrap">
<h2>Staff Details</h2>

<form method="post" action="options.php">
    <?php settings_fields( 'fumbbl-stats-settings-group' ); ?>
    <?php do_settings_sections( 'fumbbl-stats-settings-group' ); ?>
    You can find your group and tournament ids by looking at the URL bar at fumbbl.com. 
    <table class="form-table">
        <tr valign="top">
        <th scope="row">FUMBBL group id</th>
        <td><input type="text" name="group_id" value="<?php echo esc_attr( get_option('group_id') ); ?>" /></td>
        </tr>
         
        <tr valign="top">
        <th scope="row">FUMBBL tournament id</th>
        <td><input type="text" name="tournament_id" value="<?php echo esc_attr( get_option('tournament_id') ); ?>" /></td>
        </tr>
        
        <tr valign="top">
        <th scope="row">Cache Fumbbl data for (minutes)</th>
        <td><input type="text" name="cache_time" value="<?php echo esc_attr( get_option('cache_time') ); ?>" /></td>
        </tr>
        
    </table>
    
    <?php submit_button(); ?>

</form>
</div>
<?php
}

add_action( 'admin_init', 'fumbbl_stats_settings' );

function fumbbl_stats_settings() {
    register_setting( 'fumbbl-stats-settings-group', 'group_id' );
    register_setting( 'fumbbl-stats-settings-group', 'tournament_id' );
    register_setting( 'fumbbl-stats-settings-group', 'cache_time' );
}


// Shortcodes

// [fumbbl_standings_table]
// title = Header row of table
function standings_table( $atts ){
    include_once('includes/fumbbl_api.php');
    $a = shortcode_atts( array(
        'title' => 'Current standings'
    ), $atts );
    return fumbblStandingsTable($a['title']);
}

add_shortcode( 'fumbbl_standings_table', 'standings_table');

function player_table ( $atts ) {
    include_once('includes/fumbbl_api.php');
    $a = shortcode_atts( array(
        'attribute'=>'currentSpps',
        'title' => 'Top players',
        'column_label'=>'',
        'entries'=> 10
    ), $atts );
    return fumbblPlayerTable($a['attribute'], $a['title'], $a['column_label']?$a['column_label']:NULL, $a['entries'] );
}

add_shortcode( 'fumbbl_player_table', 'player_table');

function team_table ( $atts ) {
    include_once('includes/fumbbl_api.php');
    $a = shortcode_atts( array(
        'attribute'=>'touchdowns',
        'title' => 'Top teams',
        'column_label'=>''
    ), $atts );
    return fumbblTeamTable($a['attribute'], $a['title'], $a['column_label']?$a['column_label']:NULL);
}

add_shortcode( 'fumbbl_team_table', 'team_table');

function match_table ( $atts ) {
    include_once('includes/fumbbl_api.php');
    $a = shortcode_atts( array(
        'title' => 'Recent matches',
        'entries'=>20
    ), $atts );
    return fumbblMatchTable($a['title'], $a['entries']);
}

add_shortcode( 'fumbbl_match_table', 'match_table');

?>

