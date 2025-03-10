<?php
/* vim:set tabstop=4 softtabstop=4 shiftwidth=4 expandtab: */
/**
 *
 * LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
 * Copyright 2001 - 2022 Ampache.org
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 *
 */

use Ampache\Config\AmpConfig;
use Ampache\Module\Playback\Stream_Playlist;
use Ampache\Module\Playback\WebPlayer;
use Ampache\Module\Util\Ui;

header('Cache-Control: no-cache');
header('Pragma: no-cache');
header('Expires: ' . gmdate(DATE_RFC1123, time() - 1));
 ?>
<!DOCTYPE html>
<html>
<head>
<!-- Propelled by Ampache | ampache.org -->
<title><?php echo scrub_out(AmpConfig::get('site_title')); ?></title>
<meta property="og:title" content="<?php echo scrub_out(AmpConfig::get('site_title')); ?>" />
<meta property="og:image" content="<?php echo Ui::get_logo_url(); ?>"/>
<meta property="og:description" content="A web based audio/video streaming application and file manager allowing you to access your music & videos from anywhere, using almost any internet enabled device." />
<meta property="og:site_name" content="Ampache"/>
<?php
if (!isset($isShare) || (isset($isShare) && !$isShare)) {
     $stream_id = $_REQUEST['playlist_id'];
     if (is_string($stream_id) || is_integer($stream_id)) {
         $playlist = new Stream_Playlist($stream_id);
     }
 }

$isRadio      = false;
$isVideo      = false;
$isDemocratic = false;
$isRandom     = false;
$radio        = null;
if (isset($playlist)) {
    if (WebPlayer::is_playlist_radio($playlist)) {
        // Special stuff for web radio (to better handle Icecast/Shoutcast metadata ...)
        // No special stuff for now
        $isRadio = true;
        $radio   = $playlist->urls[0];
    }
    $isVideo      = WebPlayer::is_playlist_video($playlist);
    $isDemocratic = WebPlayer::is_playlist_democratic($playlist);
    $isRandom     = WebPlayer::is_playlist_random($playlist);
}
require_once Ui::find_template('show_html5_player.inc.php'); ?>
