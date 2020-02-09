<?php

// Plugin Name: INN WP 中文更新器
// Plugin URI: https://inn-studio.com/wp-cn-updater
// Description: The updater will help you update your WordPress, themes and plugins successfully if your site is located in China mainland. | 如果您的站点架设在是天朝大国内，该更新器将会成功地帮助您更新您的 WordPress、主题和插件。
// Version: 1.0.0
// Author: INN STUDIO
// Author URI: https://inn-studio.com
// PHP Requires: 7.3.0
// License: GPL-3.0 or later

declare(strict_types = 1);

namespace InnStudio\InnWpCnUpdater;

\defined('\\AUTH_KEY') || \http_response_code(403) && die;

new InnWpCnUpdater();

final class InnWpCnUpdater
{
    private const INN_DOWNLOAD_PREFIX = 'https://api.inn-studio.com/wp-cn-updater-server/?url=';

    private const WP_DOWNLOAD_HOST = 'downloads.wordpress.org';

    public function __construct()
    {
        \add_filter('plugin_action_links', [$this, 'filterActionLink'], 10, 2);
        \add_filter('site_transient_update_core', function ($transient) {
            $this->deepMap($transient);

            return $transient;
        });
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

    private function isIterable($item)
    {
        return \is_object($item) || \is_array($item) || \is_iterable($item);
    }

    private function deepMap(&$items)
    {
        if (\is_string($items)) {
            $items = $this->isWpUrl($items) ? $this->toInnUrl($items) : $items;
        }

        if ($this->isIterable($items)) {
            foreach ($items as &$item) {
                $item = $this->deepMap($item);
            }
        }

        return $items;
    }

    private function toInnUrl(string $url): string
    {
        $url = \urlencode($url);

        return self::INN_DOWNLOAD_PREFIX . "{$url}";
    }

    private function isWpUrl(string $url): bool
    {
        if ( ! \filter_var($url, \FILTER_VALIDATE_URL)) {
            return false;
        }

        return self::WP_DOWNLOAD_HOST === \parse_url($url)['host'];
    }
}
