<?php
/*
 * vim:set softtabstop=4 shiftwidth=4 expandtab:
 *
 * LICENSE: GNU Affero General Public License, version 3 (AGPL-3.0-or-later)
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

declare(strict_types=0);

namespace Ampache\Module\Application\PrivateMessage;

use Ampache\Config\ConfigContainerInterface;
use Ampache\Config\ConfigurationKeyEnum;
use Ampache\Model\ModelFactoryInterface;
use Ampache\Module\Application\ApplicationActionInterface;
use Ampache\Module\Authorization\AccessLevelEnum;
use Ampache\Module\Authorization\GuiGatekeeperInterface;
use Ampache\Module\System\Core;
use Ampache\Module\System\LegacyLogger;
use Ampache\Module\Util\UiInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Log\LoggerInterface;

final class SetIsReadAction implements ApplicationActionInterface
{
    public const REQUEST_KEY = 'set_is_read';

    private ConfigContainerInterface $configContainer;

    private UiInterface $ui;

    private LoggerInterface $logger;

    private ModelFactoryInterface $modelFactory;

    public function __construct(
        ConfigContainerInterface $configContainer,
        UiInterface $ui,
        LoggerInterface $logger,
        ModelFactoryInterface $modelFactory
    ) {
        $this->configContainer = $configContainer;
        $this->ui              = $ui;
        $this->logger          = $logger;
        $this->modelFactory    = $modelFactory;
    }

    public function run(ServerRequestInterface $request, GuiGatekeeperInterface $gatekeeper): ?ResponseInterface
    {
        if (
            $gatekeeper->mayAccess(AccessLevelEnum::TYPE_INTERFACE, AccessLevelEnum::LEVEL_USER) === false ||
            $this->configContainer->isFeatureEnabled(ConfigurationKeyEnum::SOCIABLE) === false
        ) {
            $this->logger->warning(
                'Access Denied: sociable features are not enabled.',
                [LegacyLogger::CONTEXT_TYPE => __CLASS__]
            );
            $this->ui->accessDenied();

            return null;
        }

        if ($this->configContainer->isFeatureEnabled(ConfigurationKeyEnum::DEMO_MODE) === true) {
            return null;
        }

        $this->ui->showHeader();

        $msgs = explode(',', $_REQUEST['msgs']);
        foreach ($msgs as $msg_id) {
            $pvmsg = $this->modelFactory->createPrivateMsg((int) ($msg_id));
            if ($pvmsg->id && $pvmsg->to_user === Core::get_global('user')->id) {
                $read = (int) $_REQUEST['read'];
                $pvmsg->set_is_read($read);
            } else {
                $this->logger->warning(
                    sprintf('Unknown or unauthorized private message `%d`.', $pvmsg->id),
                    [LegacyLogger::CONTEXT_TYPE => __CLASS__]
                );
                $this->ui->accessDenied();

                return null;
            }
        }

        show_confirmation(
            T_('No Problem'),
            T_('Message\'s state has been changed'),
            sprintf(
                '%s/browse.php?action=pvmsg',
                $this->configContainer->getWebPath()
            )
        );

        $this->ui->showQueryStats();
        $this->ui->showFooter();

        return null;
    }
}
