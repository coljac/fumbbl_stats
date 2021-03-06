<?php

include_once('quick_cache.php');

global $fumbbl_players, $fumbbl_teams, $tournamentObj, $tableclass;

$fumbbl_players = array();
$fumbbl_teams = array();
$tableclass = "fumbbltable";
if( get_option("table_css_class") ) {
    $tableclass = get_option("table_css_class");
}

if(file_exists(dirname(__FILE__).'/fumbbl_extensions.php'))
    include dirname(__FILE__).'/fumbbl_extensions.php';

/*
 * Fetches a URL's content with CURL. But
 * first it looks in the cache for a local version.
 */
function getUrlContents($url) {
    $result = NULL;
    if ( !isset($_GET['nocache'])) {
        $result = read_from_cache($url,  get_option("cache_time"));
    }
    if(! $result) {
        $ch = curl_init( $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        curl_setopt( $ch, CURLOPT_POST, 1 );
        $result = @curl_exec( $ch );
        write_to_cache($result, $url);
    }
    return $result;
}

/** Prints a table of players, sorted by @statistic, which is a function that takes
 * a player as an argument and returns a value to sort on.
 */
function fumbblPlayerTable( $statname, $headertext="Top players", $columntext="Value", $numEntries=10, 
    $minval=0)
{
    global $fumbbl_players, $tableclass;
    $sortedPlayers= $fumbbl_players;
    if(function_exists ( $statname )) {
        $statistic = $statname;
    } else {
        $statistic = getStat($statname);
    }
    usort( $sortedPlayers,  playerCmp($statistic) );
    $leadList = array_reverse( $sortedPlayers);
    $html = "";
    $html = $html."<table class='".$tableclass."'><tr><th colspan=5 class='".$tableclass."'>";
    $html = $html.$headertext;
    $html = $html."</th></tr>";
    $html = $html."<tr><td class='".$tableclass."'></td><td class='".$tableclass."'>Player</td><td class='".$tableclass."'>Position</td>";
    $html = $html."<td class='".$tableclass."'>Team</td><td class='".$tableclass."'>";
    $html = $html.$columntext;
    $html = $html."</td></tr>\n";
    for ( $i = 0; $i < $numEntries; $i++ )
    {
        $player = $leadList[$i];
        $stat = $statistic($player);
        if($stat<$minval) {
            continue;
        }
        $html = $html."<tr>\n";
        $html = $html."<td>\n";
        $html = $html.strval( $i+1 );
        $html = $html.". </td>\n";
        $html = $html."<td><strong>\n";
        $html = $html.'<a href="https://fumbbl.com/p/player?player_id='.$player->{'@attributes'}->id.'">'.strval( $player->name )."</a>";
        $html = $html."</strong></td>\n";
        $html = $html."<td>\n";
        $html = $html.strval( $player->position );
        $html = $html."</td>\n";
        $html = $html."<td>\n";
        $html = $html.'<a href="https://fumbbl.com/p/team?team_id='.$player->team->{'@attributes'}->id.'">'.strval( $player->team->name )."</a>";
        $html = $html."</td>\n";
        $html = $html."<td>\n";
        if (is_float( $stat )) {
            $html = $html.number_format ( $stat, 1 );
        } else {
            $html = $html.strval($stat);
        }
        $html = $html."</td>\n";
        $html = $html."</tr>\n";
    }
    $html = $html."</table>\n";
    return $html;
}

/** Prints a table of all teams, sorted by @statistic, which is a function that takes
 * a team as an argument and returns a value to sort on.
 */

function fumbblTeamTable( $statname, $headertext="Top teams", $columntext="Value", 
    $minval=0)
{
    global $fumbbl_teams, $tableclass;
    if(function_exists ( $statname )) {
        $statistic = $statname;
    } else {
        $statistic = accumstat($statname);
    }
    $html = "";
    $teamsCopy = $fumbbl_teams;
    usort( $teamsCopy, playerCmp($statistic) );
    $teamList = array_reverse( $teamsCopy );
    $html = $html."<table class='".$tableclass."'><tr><th colspan=4 class='".$tableclass."'>";
    $html = $html.$headertext;
    $html = $html."</th></tr>";
    $html = $html."<tr><td class='".$tableclass."'></td><td class='".$tableclass."'>Team</td><td class='".$tableclass."'>Coach</td>";
    $html = $html."<td class='".$tableclass."'>";
    $html = $html.$columntext;
    $html = $html."</td></tr>\n";
    for ( $i = 0; $i < count( $teamList ); $i++ )
    {
        $team = $teamList[$i];
        $rr = $statistic($team);
        if($rr<$minval) {
            continue;
        }
        $html = $html."<tr>\n";
        $html = $html."<td>\n";
        $html = $html.strval( $i+1 );
        $html = $html.". </td>\n";
        $html = $html."<td><strong>\n";
        $html = $html.'<a href="https://fumbbl.com/p/team?team_id='.$team->{'@attributes'}->id.'">'.strval( $team->name )."</a>";
        $html = $html."</strong></td>\n";
        $html = $html."<td>\n";
        $html = $html.'<a href="https://fumbbl.com/~'.strval( $team->coach ).'">'.strval( $team->coach )."</a>";
        $html = $html."</td>\n";
        $html = $html."<td>\n";
        if ( is_int( $rr) )
        {
            $html = $html.strval($rr);
        }
        else
        {
            $html = $html.number_format ( $rr, 1 );
        }
        $html = $html."</td>\n";
        $html = $html."</tr>\n";
    }
    $html = $html."</table>\n";
    return $html;
}

function fumbblStandingsTable( $headertext="League Standings")
{
    global $fumbbl_teams, $tableclass;
    $html = "";
    $sortedTeams = $fumbbl_teams;
    usort( $sortedTeams, function( $one, $two ) {
        $st1 = $one->standing;
        $st2 = $two->standing;
        $a = intval($st1->win)*30 + 10*intval($st1->tie) + intval($st1->lose);
        $b = intval($st2->win)*30 + 10*intval($st2->tie) + intval($st2->lose);
        if ( $a == $b )
        {
            $a = intval($st1->teamValue);
            $b = intval($st2->teamValue);
        }
        return  ( $a < $b ) ? -1 : 1;
    } );
    $teamList = array_reverse( $sortedTeams );
    $html = $html."<table class='".$tableclass."'><tr><th colspan=9 class='".$tableclass."'>";
    $html = $html.$headertext;
    $html = $html."</th></tr>";
    $html = $html."<tr><td class='".$tableclass."'></td><td class='".$tableclass."'>Team</td><td class='".$tableclass."'>Coach</td>";
    $html = $html."<td class='".$tableclass."'>TV</td> <td class='".$tableclass."'>Race</td> <td class='".$tableclass."' width='3%'>W</td> <td class='".$tableclass."' width='3%'>T</td> <td class='".$tableclass."' width='3%'>L</td> <td class='".$tableclass."' style='text-align: center;'>Score</td>";
    $html = $html."</tr>\n";
    for ( $i = 0; $i < count( $teamList ); $i++ )
    {
        $team = $teamList[$i];
        $html = $html."<tr>\n";
        $html = $html."<td>\n";
        $html = $html.strval( $i+1 );
        $html = $html.". </td>\n";
        $html = $html."<td><strong>\n";
        $html = $html.'<a href="https://fumbbl.com/p/team?team_id='.$team->{'@attributes'}->id.'">'.strval( $team->name )."</a>";
        $html = $html."</strong></td>\n";
        $html = $html."<td>\n";
        $html = $html.'<a href="https://fumbbl.com/~'.strval( $team->coach ).'">'.strval( $team->coach )."</a>";
        $html = $html."</td>\n";
        $html = $html."<td>\n";
        $html = $html.$team->standing->teamValue;
        $html = $html."</td>\n";
        $html = $html."<td>\n";
        $html = $html.$team->standing->race;
        $html = $html."</td>\n";
        $html = $html."<td>\n";
        $html = $html.$team->standing->win;
        $html = $html."</td>\n";
        $html = $html."<td>\n";
        $html = $html.$team->standing->tie;
        $html = $html."</td>\n";
        $html = $html."<td>\n";
        $html = $html.$team->standing->lose;
        $html = $html."</td>\n";
        $html = $html."<td style='text-align: center;'>\n";
        $html = $html.(intval($team->standing->win)*3 + intval($team->standing->tie));
        $html = $html."</td>\n";
        $html = $html."</tr>\n";
    }
    $html = $html."</table>\n";
    return $html;
}

function fumbblMatchTable( $title="Recent matches", $entries=30 ) {
    global $tournamentObj, $fumbbl_teams, $tableclass;
    $html = "";
    $html = $html. "<div class='".$tableclass."'><table class='".$tableclass."'><tr><th colspan=4 class='".$tableclass."'>";
    $html = $html. $title;
    $html = $html. "</th></tr>";
    $html = $html. "<tr><td class='".$tableclass."' style='text-align: center;'>Round</td class='".$tableclass."'> ".
        "<td class='".$tableclass."'>Home</td class='".$tableclass."'> <td class='".$tableclass."'>Away</td class='".$tableclass."'>".
        " <td class='".$tableclass."'>Result</td class='".$tableclass."'> </tr>";
    $count = 1;
    foreach ( $tournamentObj->matches->match as $match ) {
        if ( $count > $entries) {
            break;
        }
        $html = $html. "<tr><td style='text-align: center;'>";
        $html = $html. $match->{'@attributes'}->round;
        $html = $html. "</td><td><strong>";
        $team = $fumbbl_teams[$match->home->{'@attributes'}->id];
        $html = $html. '<a href="https://fumbbl.com/p/team?team_id='.$team->{'@attributes'}->id.'">'.strval( $team->name )."</a>";
        $html = $html. "</strong></td><td><strong>";
        $team = $fumbbl_teams[$match->away->{'@attributes'}->id];
        $html = $html. '<a href="https://fumbbl.com/p/team?team_id='.$team->{'@attributes'}->id.'">'.strval( $team->name )."</a>";
        $html = $html. "</strong></td><td>";
        $html = $html. "<a href='"."https://fumbbl.com/FUMBBL.php?page=match&id=".$match->{'@attributes'}->id."'>";
        $html = $html. $match->home->TD." - ".$match->away->TD;
        $html = $html. "</a>";
        $html = $html. "</td></tr>";
        $count++;
    }
    $html = $html. "</table></div>";
    return $html;
}

function playerCmp( $getStat) {
    return function ( $one, $two ) use ($getStat) {
        $a = intval($getStat($one));
        $b = intval($getStat($two));

        if ( $a == $b )
        {
            return 0;
        }
        return ( $a < $b ) ? -1 : 1;
    };
}

function getStat ( $statName ) {
    return function ( $pl ) use ($statName) {
        return $pl->playerStatistics->$statName;
    };
}

function currentSpps ( $pl ) {
    return $pl->playerStatistics->{'@attributes'}->currentSpps;
}


function accumStat( $statName ) {
    return function( $te ) use ( $statName ) {
        return accum( $te, $statName );
    };
}


function accum( $te, $statistic )
{
    $result = 0;
    foreach( $te->player as $player )
    {
        $contrib = intval( $player->playerStatistics->$statistic );
        $result = $result + $contrib;
    }
    return $result;
}

$result = getUrlContents( 'http://fumbbl.com/xml:group?id='.get_option("group_id").'&op=matches&t='.get_option("tournament_id") );
$tourney_status = getUrlContents( 'https://fumbbl.com/xml:group?id='.get_option("group_id").'&op=tourneys' );
if ( $result === FALSE )
{
    echo "CANNOT CONNECT";
}
else
{
    $tournament = simplexml_load_string( $result );
    $tournament_standings = simplexml_load_string ( $tourney_status )->xpath("/group/tournaments/tournament[@id='".get_option("tournament_id")."']");

    $standingsObj = json_decode(json_encode($tournament_standings))[0];
    $tournamentObj = json_decode(json_encode($tournament));

    foreach ( $standingsObj->members->team as $team )
    {
        $dresult = getUrlContents( 'http://fumbbl.com/xml:team?id='.$team->{'@attributes'}->id.'&past=1' );
        $teamObj = json_decode(json_encode(simplexml_load_string( $dresult )));
        $teamObj->standing = $team;
        $fumbbl_teams[$team->{'@attributes'}->id] = $teamObj;
        foreach( $teamObj->player as $player ) {
            $player->team = $team;
            $fumbbl_players[$player->{'@attributes'}->id] = $player;
        }
    }
}

?>
