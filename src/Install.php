<?php
/**
 * @brief retrocontrol, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Franck Paul and contributors
 *
 * @copyright Franck Paul carnet.franck.paul@gmail.com
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */
declare(strict_types=1);

namespace Dotclear\Plugin\retrocontrol;

use dcCore;
use dcNamespace;
use Dotclear\Core\Process;
use Exception;

class Install extends Process
{
    public static function init(): bool
    {
        return self::status(My::checkContext(My::INSTALL));
    }

    public static function process(): bool
    {
        if (!self::status()) {
            return false;
        }

        try {
            // Update
            $old_version = dcCore::app()->getVersion(My::id());
            if (version_compare((string) $old_version, '4.0', '<')) {
                // Change settings names (remove rc_ prefix in them)
                $rename = function (string $name, dcNamespace $settings): void {
                    if ($settings->settingExists('rc_' . $name, true)) {
                        $settings->rename('rc_' . $name, $name);
                    }
                };
                $settings = dcCore::app()->blog->settings->get(My::id());
                foreach (['sourceCheck', 'timeoutCheck', 'recursive', 'timeout'] as $name) {
                    $rename($name, $settings);
                }
            }

            // New install / update (just settings but not their values)
            $settings = dcCore::app()->blog->settings->get(My::id());
            $settings->put('sourceCheck', false, dcNamespace::NS_BOOL, 'Check trackback source', false, true);
            $settings->put('timeoutCheck', false, dcNamespace::NS_BOOL, 'Use disposable URL for trackbacks', false, true);
            $settings->put('recursive', true, dcNamespace::NS_BOOL, 'Recursive filtering while checking source', false, true);
            $settings->put('timeout', 300, dcNamespace::NS_INT, 'Trackback URL time life (in seconds)', false, true);
        } catch (Exception $e) {
            dcCore::app()->error->add($e->getMessage());
        }

        return true;
    }
}
