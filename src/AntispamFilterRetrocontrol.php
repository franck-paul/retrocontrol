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
use dcPage;
use Dotclear\Helper\Html\Form\Checkbox;
use Dotclear\Helper\Html\Form\Form;
use Dotclear\Helper\Html\Form\Label;
use Dotclear\Helper\Html\Form\Number;
use Dotclear\Helper\Html\Form\Para;
use Dotclear\Helper\Html\Form\Submit;
use Dotclear\Helper\Network\Http;
use Dotclear\Plugin\antispam\SpamFilter;
use Exception;

class AntispamFilterRetrocontrol extends SpamFilter
{
    public $name    = 'Rétrocontrôle';
    public $has_gui = true;

    private bool $sourceCheck  = false;
    private bool $timeoutCheck = false;
    private bool $recursive    = true;
    private $timeout           = 300;

    protected function setInfo()
    {
        $this->description = __('Trackback source check');

        $settings = dcCore::app()->blog->settings->get(My::id());
        if ($settings->sourceCheck !== null) {
            $this->sourceCheck = (bool) $settings->sourceCheck;
        }
        if ($settings->timeoutCheck !== null) {
            $this->timeoutCheck = (bool) $settings->timeoutCheck;
        }
        if ($settings->recursive !== null) {
            $this->recursive = (bool) $settings->recursive;
        }
        if ($settings->timeout) {
            $this->timeout = abs((int) $settings->timeout);
        }
    }

    public function isSpam($type, $author, $email, $site, $ip, $content, $post_id, &$status)
    {
        if ($type !== 'trackback') {
            return;
        }

        if ($this->sourceCheck && (new Retrocontrol())->checkSource($site, 0, $this->recursive)) {
            return true;
        }
    }

    public function getStatusMessage($status, $comment_id)
    {
        return sprintf(__('Filtered by %s.'), $this->guiLink());
    }

    public function gui(string $url): string
    {
        if (isset($_POST['rc_send'])) {
            try {
                $this->sourceCheck  = empty($_POST['rc_sourceCheck']) ? false : true;
                $this->timeoutCheck = empty($_POST['rc_timeoutCheck']) ? false : true;
                $this->recursive    = empty($_POST['rc_recursive']) ? false : true;
                $this->timeout      = empty($_POST['rc_timeout']) ? $this->timeout : abs((int) $_POST['rc_timeout']) * 60;

                $settings = dcCore::app()->blog->settings->get(My::id());
                $settings->put('sourceCheck', $this->sourceCheck, 'boolean');
                $settings->put('timeoutCheck', $this->timeoutCheck, 'boolean');
                $settings->put('recursive', $this->recursive, 'boolean');
                $settings->put('timeout', $this->timeout, 'integer');

                dcCore::app()->blog->triggerBlog();
                dcPage::addSuccessNotice(__('Filter configuration have been successfully saved.'));
                Http::redirect($url);
            } catch (Exception $e) {
                dcCore::app()->error->add($e->getMessage());
            }
        } else {
            return $this->showForm($url);
        }

        return '';
    }

    private function showForm(string $url): string
    {
        return dcPage::jsModuleLoad('retrocontrol/js/settings.js', dcCore::app()->getVersion('retrocontrol')) .

        (new Form('retrocontrol-form'))
        ->action($url)
        ->method('post')
        ->fields([
            (new Para())->items([
                (new Checkbox('rc_sourceCheck', $this->sourceCheck))
                    ->value(1)
                    ->label((new Label(__('Verify trackback source'), Label::INSIDE_TEXT_AFTER))),
            ]),
            (new Para())->items([
                (new Checkbox('rc_recursive', $this->recursive))
                    ->value(1)
                    ->label((new Label(__('Allow recursive filtering'), Label::INSIDE_TEXT_AFTER))),
            ]),
            (new Para())->items([
                (new Checkbox('rc_timeoutCheck', $this->timeoutCheck))
                    ->value(1)
                    ->label((new Label(__('Active disposable addresses for trackbacks'), Label::INSIDE_TEXT_AFTER))),
            ]),
            (new Para())->items([
                (new Number('rc_timeout', 1, 9999, (int) $this->timeout / 60))
                    ->label((new Label(__('Trackback address life time (in minutes):'), Label::INSIDE_TEXT_BEFORE))),
            ]),
            (new Para())->items([
                (new Submit(['rc_send'], __('Save')))
                    ->accesskey('s'),
                dcCore::app()->formNonce(false),
            ]),
        ])
        ->render();
    }
}
