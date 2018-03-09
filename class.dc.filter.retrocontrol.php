<?php
/**
 * @brief retrocontrol, a plugin for Dotclear 2
 *
 * @package Dotclear
 * @subpackage Plugins
 *
 * @author Oleksandr Syenchuk
 * @author Alain Vagner
 *
 * @copyright Oleksandr Syenchuk, Alain Vagner
 * @copyright GPL-2.0 https://www.gnu.org/licenses/gpl-2.0.html
 */

class dcFilterRetrocontrol extends dcSpamFilter
{
    public $name    = 'Rétrocontrôle';
    public $has_gui = true;

    private $rc_sourceCheck  = false;
    private $rc_timeoutCheck = false;
    private $rc_recursive    = true;
    private $rc_timeout      = 300;

    protected function setInfo()
    {
        $this->description = __('Trackback source check');

        $this->core->blog->settings->addNamespace('retrocontrol');
        if ($this->core->blog->settings->retrocontrol->rc_sourceCheck !== null) {
            $this->rc_sourceCheck = (bool) $this->core->blog->settings->retrocontrol->rc_sourceCheck;
        }
        if ($this->core->blog->settings->retrocontrol->rc_timeoutCheck !== null) {
            $this->rc_timeoutCheck = (bool) $this->core->blog->settings->retrocontrol->rc_timeoutCheck;
        }
        if ($this->core->blog->settings->retrocontrol->rc_recursive !== null) {
            $this->rc_recursive = (bool) $this->core->blog->settings->retrocontrol->rc_recursive;
        }
        if ($this->core->blog->settings->retrocontrol->rc_timeout) {
            $this->rc_timeout = abs((int) $this->core->blog->settings->retrocontrol->rc_timeout);
        }
    }

    public function isSpam($type, $author, $email, $site, $ip, $content, $post_id, &$status)
    {
        if ($type != 'trackback') {
            return;
        }

        $t = new retrocontrol;

        if ($this->rc_sourceCheck && $t->checkSource($site, 0, $this->rc_recursive)) {
            return true;
        }

        return;
    }

    public function getStatusMessage($status, $comment_id)
    {
        return sprintf(__('Filtered by %s.'), $this->guiLink());
    }

    public function gui($url)
    {
        $this->core->blog->settings->addNamespace('retrocontrol');
        if (isset($_POST['rc_send'])) {
            try
            {
                $this->rc_sourceCheck  = empty($_POST['rc_sourceCheck']) ? false : true;
                $this->rc_timeoutCheck = empty($_POST['rc_timeoutCheck']) ? false : true;
                $this->rc_recursive    = empty($_POST['rc_recursive']) ? false : true;
                $this->rc_timeout      = empty($_POST['rc_timeout']) ? $this->rc_timeout : abs((int) $_POST['rc_timeout']) * 60;

                $this->core->blog->settings->retrocontrol->put('rc_sourceCheck', $this->rc_sourceCheck, 'boolean');
                $this->core->blog->settings->retrocontrol->put('rc_timeoutCheck', $this->rc_timeoutCheck, 'boolean');
                $this->core->blog->settings->retrocontrol->put('rc_recursive', $this->rc_recursive, 'boolean');
                $this->core->blog->settings->retrocontrol->put('rc_timeout', $this->rc_timeout, 'integer');

                $this->core->blog->triggerBlog();
                dcPage::addSuccessNotice(__('Filter configuration have been successfully saved.'));
                http::redirect($url);
            } catch (Exception $e) {
                $this->core->error->add($e->getMessage());
            }
        } else {
            return $this->showForm($url);
        }
    }

    private function showForm($url)
    {
        $res =
        '<script type="text/javascript">' . "\n" .
        '$(function() {' . "\n" .
        ' $("#rc_sourceCheck").change(function()' . "\n" .
        ' {' . "\n" .
        '   if (this.checked)' . "\n" .
        '     $("#sourceConfig").show();' . "\n" .
        '   else' . "\n" .
        '     $("#sourceConfig").hide();' . "\n" .
        ' });' . "\n" .
        ' ' . "\n" .
        ' if (!document.getElementById("rc_sourceCheck").checked)' . "\n" .
        '   $("#sourceConfig").hide();' . "\n" .

        ' $("#rc_timeoutCheck").change(function()' . "\n" .
        ' {' . "\n" .
        '   if (this.checked)' . "\n" .
        '     $("#timeoutConfig").show();' . "\n" .
        '   else' . "\n" .
        '     $("#timeoutConfig").hide();' . "\n" .
        ' });' . "\n" .
        ' ' . "\n" .
        ' if (!document.getElementById("rc_timeoutCheck").checked)' . "\n" .
        '   $("#timeoutConfig").hide();' . "\n" .
        '});' . "\n" .
        '</script>' . "\n" .

        '<form method="post" action="' . $url . '">' .

        '<p><label class="classic">' .
        '<input type="checkbox" name="rc_sourceCheck" id="rc_sourceCheck" value="1"' .
        ($this->rc_sourceCheck ? ' checked="checked"' : '') . ' /> ' .
        __('Verify trackback source') .
        '</label></p>' .

        '<p id="sourceConfig" style="padding-left:1em;"><label class="classic">' .
        '<input type="checkbox" name="rc_recursive" id="rc_recursive" value="1"' .
        ($this->rc_recursive ? ' checked="checked"' : '') . ' /> ' .
        __('Allow recursive filtering') .
        '</label></p>' .

        '<p><label class="classic">' .
        '<input type="checkbox" name="rc_timeoutCheck" id="rc_timeoutCheck" value="1"' .
        ($this->rc_timeoutCheck ? ' checked="checked"' : '') . ' /> ' .
        __('Active disposable addresses for trackbacks') .
        '</label></p>' .

        '<p id="timeoutConfig" style="padding-left:1em;"><label class="classic">' .
        __('Trackback address life time (in minutes):') . ' ' .
        '<input type="text" name="rc_timeout" id="rc_timeout" size="3" maxlength="3" value="' .
        ($this->rc_timeout ? ((int) $this->rc_timeout / 60) : '') . '" />' .
        '</label></p>' .

        '<p><input type="submit" name="rc_send" value="' . __('Save') . '" />' .
            (is_callable(array($this->core, 'formNonce')) ? $this->core->formNonce() : '') . '</p>' .

            '</form>';

        return $res;
    }
}
