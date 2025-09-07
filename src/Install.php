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

use Dotclear\App;
use Dotclear\Helper\Process\TraitProcess;
use Dotclear\Interface\Core\BlogWorkspaceInterface;
use Exception;

class Install
{
    use TraitProcess;

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
            $old_version = App::version()->getVersion(My::id());
            if (version_compare((string) $old_version, '4.0', '<')) {
                // Change settings names (remove rc_ prefix in them)
                $rename = static function (string $name, BlogWorkspaceInterface $settings): void {
                    if ($settings->settingExists('rc_' . $name, true)) {
                        $settings->rename('rc_' . $name, $name);
                    }
                };
                $settings = My::settings();
                foreach (['sourceCheck', 'timeoutCheck', 'recursive', 'timeout'] as $name) {
                    $rename($name, $settings);
                }
            }

            // New install / update (just settings but not their values)
            $settings = My::settings();
            $settings->put('sourceCheck', false, App::blogWorkspace()::NS_BOOL, 'Check trackback source', false, true);
            $settings->put('timeoutCheck', false, App::blogWorkspace()::NS_BOOL, 'Use disposable URL for trackbacks', false, true);
            $settings->put('recursive', true, App::blogWorkspace()::NS_BOOL, 'Recursive filtering while checking source', false, true);
            $settings->put('timeout', 300, App::blogWorkspace()::NS_INT, 'Trackback URL time life (in seconds)', false, true);
        } catch (Exception $exception) {
            App::error()->add($exception->getMessage());
        }

        return true;
    }
}
