<?php

// Plugin Name: INN WP 中文更新器
// Plugin URI: https://inn-studio.com/wp-cn-updater
// Description: The plugin will help you update your WordPress, themes and plugins successfully if your site is located in China mainland. | 如果您的站点架设在是天朝大国内，该插件将会成功地帮助您更新您的 WordPress、主题和插件。
// Version: 2.0.0
// Author: INN STUDIO
// Author URI: https://inn-studio.com
// PHP Requires: 7.3.0
// License: GPL-3.0 or later

declare(strict_types = 1);

namespace InnStudio\Plugin\InnWpCnUpdater;

\defined('\\AUTH_KEY') || \http_response_code(403) && die;

new InnWpCnUpdater();

final class InnWpCnUpdater
{
    public const ID = 'innWpCnUpdater';

    public const VERSION = '2.0.0';

    private const REPLACE_API_WP_ORG = 'api-wordpress-org.inn-studio.com';

    private const REPLACE_DL_WP_ORG = 'downloads-wordpress-org.inn-studio.com';

    private const REPLACE_GRA = 'https://ga.inn-studio.com/avatar';

    private const REPLACE_AJAX_GG_COM = 'ajax-googleapis-com.inn-studio.com';

    private const REPLACE_FONT_GG_COM = 'fonts-googleapis-com.inn-studio.com';

    private const MATCH_DL_WP_ORG = 'downloads.wordpress.org';

    private const MATCH_API_WP_ORG = 'api.wordpress.org';

    private const MATCH_GRA = 'gravatar.com/avatar';

    private const MATCH_AJAX_GG_COM = 'ajax.googleapis.com';

    private const MATCH_FONT_GG_COM = 'fonts.googleapis.com';

    public function __construct()
    {
        \add_filter('plugin_action_links', [$this, 'filterActionLink'], 10, 2);
        \add_filter('pre_http_request', [$this, 'filterPreHttpRequest'], 10, 3);
        \add_filter('get_avatar_url', [$this, 'filterGetAvatarUrl']);
    }

    public function filterActionLink($actions, string $pluginFile): array
    {
        if (false !== \stripos($pluginFile, \basename(__DIR__))) {
            $opts = <<<'HTML'
<a href="https://github.com/kmvan/wp-plugin-cn-updater" target="_blank" title="查看该开源项目" class="button" style="line-height: 1.5; min-height: auto;">GitHub</a>
<a href="https://cdn.inn-studio.com/themes/common/inn-alipay.jpg" target="_blank" title="点击弹出二维码" class="button" style="line-height: 1.5; min-height: auto;">微信打赏</a>
<a href="https://cdn.inn-studio.com/themes/common/inn-wechat.jpg" target="_blank" title="点击弹出二维码" class="button" style="line-height: 1.5; min-height: auto;">支付宝打赏</a>
HTML;

            if ( ! \is_array($actions)) {
                $actions = [];
            }

            \array_unshift($actions, $opts);
        }

        return $actions;
    }

    public function filterPreHttpRequest(bool $preempt, array $parseArgs, string $url)
    {
        if (false !== $preempt) {
            return $preempt;
        }

        switch (true) {
            case false !== \strpos($url, self::REPLACE_API_WP_ORG):
            case false !== \strpos($url, self::REPLACE_DL_WP_ORG):
            case false !== \strpos($url, self::REPLACE_AJAX_GG_COM):
            case false !== \strpos($url, self::REPLACE_FONT_GG_COM):
                return $preempt;
        }

        switch (true) {
            case false !== \strpos($url, self::MATCH_API_WP_ORG):
            case false !== \strpos($url, self::MATCH_DL_WP_ORG):
            case false !== \strpos($url, self::MATCH_AJAX_GG_COM):
            case false !== \strpos($url, self::MATCH_FONT_GG_COM):
                return \wp_remote_request($this->replaceUrl($url), $parseArgs);
        }

        return $preempt;
    }

    public function filterGetAvatarUrl(string $url): string
    {
        return \preg_replace('/http.+\\.gravatar\\.com\\/avatar/i', self::REPLACE_GRA, $url);
    }

    private function toSsl(string $url): string
    {
        return \str_replace('http://', 'https://', $url);
    }

    private function replaceUrl(string $url): string
    {
        $host = \parse_url($url)['host'] ?? '';

        if ( ! $host) {
            return $url;
        }

        return \str_replace([
            self::MATCH_API_WP_ORG,
            self::MATCH_DL_WP_ORG,
            self::MATCH_GRA,
            self::MATCH_AJAX_GG_COM,
            self::MATCH_FONT_GG_COM,
        ], [
            self::REPLACE_API_WP_ORG,
            self::REPLACE_DL_WP_ORG,
            self::REPLACE_GRA,
            self::REPLACE_AJAX_GG_COM,
            self::REPLACE_FONT_GG_COM,
        ], $this->toSsl($url));
    }
}
