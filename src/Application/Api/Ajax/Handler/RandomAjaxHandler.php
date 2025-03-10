<?php

/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
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

declare(strict_types=0);

namespace Ampache\Application\Api\Ajax\Handler;

use Ampache\Repository\Model\Album;
use Ampache\Config\AmpConfig;
use Ampache\Repository\Model\Browse;
use Ampache\Module\System\Core;
use Ampache\Repository\Model\Playlist;
use Ampache\Repository\Model\Random;
use Ampache\Module\Util\Ui;
use Ampache\Repository\AlbumRepositoryInterface;
use Ampache\Repository\SongRepositoryInterface;

final class RandomAjaxHandler implements AjaxHandlerInterface
{
    private AlbumRepositoryInterface $albumRepository;

    private SongRepositoryInterface $songRepository;

    public function __construct(
        AlbumRepositoryInterface $albumRepository,
        SongRepositoryInterface $songRepository
    ) {
        $this->albumRepository = $albumRepository;
        $this->songRepository  = $songRepository;
    }

    public function handle(): void
    {
        $results = array();
        $songs   = array();

        // Switch on the actions
        switch ($_REQUEST['action']) {
            case 'song':
                $songs = Random::get_default(null, Core::get_global('user'));

                if (!count($songs)) {
                    $results['rfc3514'] = '0x1';
                    break;
                }

                foreach ($songs as $song_id) {
                    Core::get_global('user')->playlist->add_object($song_id, 'song');
                }
                $results['rightbar'] = Ui::ajax_include('rightbar.inc.php');
                break;
            case 'album':
                $album_id = $this->albumRepository->getRandom(
                    Core::get_global('user')->id ?? -1,
                    null
                );

                if (empty($album_id)) {
                    $results['rfc3514'] = '0x1';
                    break;
                }

                $album = new Album($album_id[0]);
                // songs for all disks
                if (AmpConfig::get('album_group')) {
                    $disc_ids = $album->get_group_disks_ids();
                    foreach ($disc_ids as $discid) {
                        $disc     = new Album($discid);
                        $allsongs = $this->songRepository->getByAlbum($disc->id);
                        foreach ($allsongs as $songid) {
                            $songs[] = $songid;
                        }
                    }
                } else {
                    // songs for just this disk
                    $songs = $this->songRepository->getByAlbum($album->id);
                }
                foreach ($songs as $song_id) {
                    Core::get_global('user')->playlist->add_object($song_id, 'song');
                }
                $results['rightbar'] = Ui::ajax_include('rightbar.inc.php');
                break;
            case 'artist':
                $artist_id = Random::artist();

                if (!$artist_id) {
                    $results['rfc3514'] = '0x1';
                    break;
                }

                $songs  = $this->songRepository->getByArtist($artist_id);
                foreach ($songs as $song_id) {
                    Core::get_global('user')->playlist->add_object($song_id, 'song');
                }
                $results['rightbar'] = Ui::ajax_include('rightbar.inc.php');
                break;
            case 'playlist':
                $playlist_id = Random::playlist();

                if (!$playlist_id) {
                    $results['rfc3514'] = '0x1';
                    break;
                }

                $playlist = new Playlist($playlist_id);
                $items    = $playlist->get_items();
                foreach ($items as $item) {
                    Core::get_global('user')->playlist->add_object($item['object_id'], $item['object_type']);
                }
                $results['rightbar'] = Ui::ajax_include('rightbar.inc.php');
                break;
            case 'send_playlist':
                $_SESSION['iframe']['target'] = AmpConfig::get('web_path') . '/stream.php?action=random' . '&random_type=' . scrub_out($_REQUEST['random_type']) . '&random_id=' . scrub_out($_REQUEST['random_id']);
                $results['rfc3514']           = '<script>' . Core::get_reloadutil() . '("' . $_SESSION['iframe']['target'] . '")</script>';
                break;
            case 'advanced_random':
                $object_ids = Random::advanced('song', $_POST);

                // First add them to the active playlist
                if (!empty($object_ids)) {
                    foreach ($object_ids as $object_id) {
                        Core::get_global('user')->playlist->add_object($object_id, 'song');
                    }
                }
                $results['rightbar'] = Ui::ajax_include('rightbar.inc.php');

                // Now setup the browse and show them below!
                $browse = new Browse();
                $browse->set_type('song');
                $browse->save_objects($object_ids);
                ob_start();
                $browse->show_objects();
                $results['browse'] = ob_get_contents();
                ob_end_clean();
                break;
            default:
                $results['rfc3514'] = '0x1';
                break;
        } // switch on action;

        // We always do this
        echo (string) xoutput_from_array($results);
    }
}
