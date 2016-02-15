# Fummbl Stats plugin for Wordpress

## Introduction

This plugin will fetch information about the teams and players in your league and display tables for you in your posts and pages.

## Installation

To install the plugin, either:

- Download the zip file, and unzip it in `[wordpress root]/wp-content/plugins`, or;
- Clone this repository into the same directory, with `git clone https://github.com/coljac/fumbbl_stats.git fumbbl_stats`

You should now find the plugin in the Wordpress administration plugins page. Activate it.

## Configuration

You'll need to know your group id and tournament id from Fumbbl.com. To find them, go to the tournament page and record these numbers from the URLs that Fumbbl creates, for instance:

  https://fumbbl.com/p/group?op=view&group=9968&p=tournaments

You need to get the tournament id from the links that say edit, delete, configure on that page. (In future, it might be nice to automate this.)

Note you don't have to be the administrator of a tournament to set this up, since it's just reading public data from Fumbbl.

After activating the plugin a new menu option should appear. Enter the group and tournament ids in the box and save them.

The table will have a CSS class of "fumbbltable" by default. You need to add that to your own styles if you want to tweak it, or change the table class with the config option specified.

## Caching the data

Rendering a single table might require fetching a dozen pages from Fumbbl.com, so for reasons of speed and politeness it's highly encouraged to cache these results for a period of time. By default, the cache lasts for a day. However, you might want to set a longer value and clear it at the end of the round if you have a round-robin style of league.

You can change the cache settings and clear the cache from the configuration page. A cache time of 0 will mean no caching is performed. Again, this will be slow and will hammer Fumbbl's servers a bit.

## Using the plugin

The plugin adds several shortcodes that you can use in pages and posts, that when displayed will print tables showing statistics from the league - in particular, top players, top teams, recent matches and the tournament leaderboard.

For each of these shortcodes, the attributes are optional.

- `[fumbbl_standings_table title='Current standings]`: Shows the league standings as of the current point in time. Note that as it currently stands it will update mid-round, so if you want it to only show the standings at the end of a round, cache the results then.
- `[fumbbl_player_table attribute='casualties' title='Top hitters' entries='10']`: Shows the top players by named attribute. Valid attribute names are listed below.
- `[fumbbl_team_table attribute='touchdowns' title='Most touchdowns']`: Sorts the teams in the league by the the cumulative attribute across the teams players. Valid additrubes are as above.
- `[fumbbl_matches_table title='Recent matches] entries='20']`: Lists recent matches.

In each case, the rendered tables will include hyperlinks to teams, coaches, players and matches back on fumbbl.com.

### Attributes

Valid attributes for the player and team stats are 'touchdowns', 'casualties', 'blocks', 'interceptions', 'fouls', 'rushing', 'passing', 'completions', 
games', 'mvps', and 'currentSpps'. The default for players is SPPs.

## Extending the plugin

Of course the source code is available for you to hack at will. If you want to show a table with a team or player statistic not featured (but one that you can calculate from existing data or some other source), you can add that functionality pretty quickly.  The plugin will look for a file called `fumbbl_extensions.php` in the `includes` folder of the plugin install directory. You can add a function that takes a player or team object and returns a value to sort on and display. Pass that function's name to the shortcodes above as the attribute to sort on. An example is given below:

    function block_efficiency ( $player ) {
        if($player->playerStatistics->blocks > 0) {
            return 10*($player->playerStatistics->casualties / $player->playerStatistics->blocks);
        }
        return 0;
    }

This function could be used as follows: `[fumbbl_player_table attribute='block_efficiency' title='Luckiest blockers']`

## Feedback

If you use the plugin or have any queries please contact Colin Jacobs at colin@coljac.net or submit an issue through GitHub.
