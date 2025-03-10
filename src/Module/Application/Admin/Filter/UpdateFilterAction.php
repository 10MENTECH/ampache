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

namespace Ampache\Module\Application\Admin\Filter;

use Ampache\Config\ConfigContainerInterface;
use Ampache\Config\ConfigurationKeyEnum;
use Ampache\Module\Application\Exception\AccessDeniedException;
use Ampache\Module\System\AmpError;
use Ampache\Module\System\Core;
use Ampache\Module\Util\Ui;
use Ampache\Module\Util\UiInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Ampache\Repository\Model\Catalog;

final class UpdateFilterAction extends AbstractFilterAction
{
    public const REQUEST_KEY = 'update_filter';

    private UiInterface $ui;
    private ConfigContainerInterface $configContainer;

    public function __construct(
        UiInterface $ui,
        ConfigContainerInterface $configContainer
    ) {
        $this->ui              = $ui;
        $this->configContainer = $configContainer;
    }

    protected function handle(ServerRequestInterface $request): ?ResponseInterface
    {
        if ($this->configContainer->isFeatureEnabled(ConfigurationKeyEnum::DEMO_MODE) === true) {
            return null;
        }

        if (!Core::form_verify('update_filter')) {
            throw new AccessDeniedException();
        }

        $this->ui->showHeader();

        $filter_id   = filter_input(INPUT_POST, 'filter_id', FILTER_SANITIZE_NUMBER_INT);
        $filter_name = ($filter_id == 0)
            ? 'DEFAULT'
            : (string) scrub_in(filter_input(INPUT_POST, 'name', FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES));

        if (empty($filter_name)) {
            AmpError::add('name', T_('A filter name is required'));
        }

        // make sure the filter doesn't already exist
        if ((Catalog::filter_name_exists($filter_name, $filter_id))) {
            AmpError::add('name', T_('That filter name already exists'));
        }

        // If we've got an error then show add form!
        if (AmpError::occurred()) {
            require_once Ui::find_template('show_edit_filter.inc.php');

            $this->ui->showQueryStats();
            $this->ui->showFooter();

            return null;
        }

        $catalogs      = Catalog::get_catalogs();
        $catalog_array = array();
        foreach ($catalogs as $catalog_id) {
            $catalog_status             = (int)filter_input(INPUT_POST, 'catalog_' . $catalog_id, FILTER_SANITIZE_NUMBER_INT);
            $catalog_array[$catalog_id] = $catalog_status;
        }
        // Attempt to modify the filter
        if (!Catalog::edit_catalog_filter($filter_id, $filter_name, $catalog_array)) {
            AmpError::add('general', T_("The filter was not modified"));
        }

        $this->ui->showConfirmation(
            T_('Filter Updated'),
            sprintf(T_('%1$s has been updated'), $filter_name),
            sprintf('%s/admin/filter.php', $this->configContainer->getWebPath())
        );

        $this->ui->showQueryStats();
        $this->ui->showFooter();

        return null;
    }
}
