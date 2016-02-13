<?php
    require_once('Cache/Lite.php');
    $lifetime = get_option("cache_time");

    if(!$lifetime) {
        $lifetime = 60; // An hour
    }
    // Set a id for this cache
    $id = 'fumbbl_cached';
    
    // Set a few options
    $options = array(
        'cacheDir' => '/tmp/',
        'lifeTime' => $lifetime * 60 // in seconds
    );

    $Cache_Lite = new Cache_Lite($options);
    if ( isset($_GET['nocache'])) {
      $Cache_Lite->clean();
      echo("<!-- cleared cache -->");
    }
/* string sys_get_temp_dir ( void ) */
?>

