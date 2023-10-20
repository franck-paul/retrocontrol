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
use Dotclear\Core\Backend\Notices;
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
    /** @var string Filter name */
    public string $name = 'Rétrocontrôle';

    /** @var bool Filter has settings GUI? */
    public bool $has_gui = true;

    private bool $sourceCheck  = false;
    private bool $timeoutCheck = false;
    private bool $recursive    = true;
    private int $timeout       = 300;

    /**
     * Sets the filter description.
     */
    protected function setInfo(): void
    {
        $this->description = __('Trackback source check');

        $settings = My::settings();
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

    /**
     * This method should return if a comment is a spam or not. If it returns true
     * or false, execution of next filters will be stoped. If should return nothing
     * to let next filters apply.
     *
     * Your filter should also fill $status variable with its own information if
     * comment is a spam.
     *
     * @param      string  $type     The comment type (comment / trackback)
     * @param      string  $author   The comment author
     * @param      string  $email    The comment author email
     * @param      string  $site     The comment author site
     * @param      string  $ip       The comment author IP
     * @param      string  $content  The comment content
     * @param      int     $post_id  The comment post_id
     * @param      string  $status   The comment status
     */
    public function isSpam(string $type, ?string $author, ?string $email, ?string $site, ?string $ip, ?string $content, ?int $post_id, string &$status)
    {
        if ($type !== 'trackback') {
            return;
        }

        if ($this->sourceCheck && (new Retrocontrol())->checkSource((string) $site, null, $this->recursive)) {
            return true;
        }
    }

    /**
     * This method returns filter status message. You can overload this method to
     * return a custom message. Message is shown in comment details and in
     * comments list.
     *
     * @param      string  $status      The status
     * @param      int     $comment_id  The comment identifier
     *
     * @return     string  The status message.
     */
    public function getStatusMessage(string $status, ?int $comment_id): string
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

                $settings = My::settings();
                $settings->put('sourceCheck', $this->sourceCheck, 'boolean');
                $settings->put('timeoutCheck', $this->timeoutCheck, 'boolean');
                $settings->put('recursive', $this->recursive, 'boolean');
                $settings->put('timeout', $this->timeout, 'integer');

                App::blog()->triggerBlog();
                Notices::addSuccessNotice(__('Filter configuration have been successfully saved.'));
                Http::redirect($url);
            } catch (Exception $e) {
                App::error()->add($e->getMessage());
            }
        } else {
            return $this->showForm($url);
        }

        return '';
    }

    private function showForm(string $url): string
    {
        return My::jsLoad('settings.js') .

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
                (new Number('rc_timeout', 1, 9999, (int) ($this->timeout / 60)))
                    ->label((new Label(__('Trackback address life time (in minutes):'), Label::INSIDE_TEXT_BEFORE))),
            ]),
            (new Para())->items([
                (new Submit(['rc_send'], __('Save')))
                    ->accesskey('s'),
                ... My::hiddenFields(),
            ]),
        ])
        ->render();
    }
}
