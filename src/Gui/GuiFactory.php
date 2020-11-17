<?php
/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 *  LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
 * Copyright 2001 - 2020 Ampache.org
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

declare(strict_types=1);

namespace Ampache\Gui;

use Ampache\Config\ConfigContainerInterface;
use Ampache\Gui\Catalog\CatalogDetails;
use Ampache\Gui\Catalog\CatalogDetailsInterface;
use Ampache\Gui\Song\SongViewAdapter;
use Ampache\Gui\Song\SongViewAdapterInterface;
use Ampache\Gui\Stats\CatalogStats;
use Ampache\Gui\Stats\CatalogStatsInterface;
use Ampache\Gui\Stats\StatsViewAdapter;
use Ampache\Gui\Stats\StatsViewAdapterInterface;
use Ampache\Gui\System\ConfigViewAdapter;
use Ampache\Gui\System\ConfigViewAdapterInterface;
use Ampache\Gui\System\UpdateViewAdapter;
use Ampache\Gui\System\UpdateViewAdapterInterface;
use Ampache\Model\Catalog;
use Ampache\Model\ModelFactoryInterface;
use Ampache\Model\Song;

final class GuiFactory implements GuiFactoryInterface
{
    private ConfigContainerInterface $configContainer;

    private ModelFactoryInterface $modelFactory;

    public function __construct(
        ConfigContainerInterface $configContainer,
        ModelFactoryInterface $modelFactory
    ) {
        $this->configContainer = $configContainer;
        $this->modelFactory    = $modelFactory;
    }

    public function createSongViewAdapter(
        Song $song
    ): SongViewAdapterInterface {
        return new SongViewAdapter(
            $this->configContainer,
            $this->modelFactory,
            $song
        );
    }

    public function createConfigViewAdapter(): ConfigViewAdapterInterface
    {
        return new ConfigViewAdapter(
            $this->configContainer
        );
    }
    
    public function createStatsViewAdapter(): StatsViewAdapterInterface
    {
        return new StatsViewAdapter(
            $this->configContainer,
            $this
        );
    }
    
    public function createCatalogDetails(
        Catalog $catalog
    ): CatalogDetailsInterface {
        return new CatalogDetails(
            $this,
            $catalog
        );
    }
    
    public function createCatalogStats(array $stats): CatalogStatsInterface
    {
        return new CatalogStats($stats);
    }
    
    public function createUpdateViewAdapter(): UpdateViewAdapterInterface
    {
        return new UpdateViewAdapter(
            $this->configContainer
        );
    }
}
