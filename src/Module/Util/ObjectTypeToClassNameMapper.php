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

declare(strict_types=1);

namespace Ampache\Module\Util;

use Ampache\Model\Album;
use Ampache\Model\Song;

/**
 * This class maps object types like `album` to their corresponding php class name (if known)
 *
 * @deprecated Remove after every usage has been removed
 */
final class ObjectTypeToClassNameMapper
{
    private const OBJECT_TYPE_MAPPING = [
        'album' => Album::class,
        'song' => Song::class,
    ];

    public static function map(string $object_type)
    {
        return self::OBJECT_TYPE_MAPPING[$object_type] ?? $object_type;
    }

    public static function reverseMap(string $class_name): string
    {
        return array_flip(self::OBJECT_TYPE_MAPPING)[$class_name] ?? $class_name;
    }
}
