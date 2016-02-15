<?php
function write_to_cache($data, $id, $lifetime) {
    $data = "cache-time: ".strval(time())."\n".$data;
    $filename = hash( 'md5', 'fumbbl_stats'.$id);
    file_put_contents ( sys_get_temp_dir()."/".$filename , $data );
}

function read_from_cache($id, $cache_lifetime) {
    $filename = sys_get_temp_dir()."/".$filename.hash ('md5', 'fumbbl_stats'.$id);
    if (file_exists ($filename)) {
        $data = file_get_contents ($filename);
        $now = time();
        $header = strtok($data, "\n");
        strtok( $header, ":");
        $cached_time = strtok( ":");
        if (intval($cached_time) + intval($cache_lifetime)*60 > $now) { # Minutes to seconds
            return substr( $data, strpos($data, "\n")+1 );
        } else {
            unlink($filename);
        }
    }
    return NULL;
}
?>
