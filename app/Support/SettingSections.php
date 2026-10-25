<?php

namespace App\Support;

class SettingSections
{
    public const LABELS = [
        'site' => '网站信息', 'look' => '网站外观', 'interact' => '评论规则',
        'urls' => '内容地址', 'uploads' => '图片上传', 'mail' => '邮件通知',
        'api' => '加速与接口', 'seo' => '搜索与统计',
    ];

    public const FIELDS = [
        'site' => ['site_name', 'site_description', 'site_keywords', 'site_closed', 'site_closed_message'],
        'look' => ['theme', 'mobile_theme', 'mobile_domain', 'mobile_detect', 'logo_file', 'logo_clear', 'favicon_file', 'favicon_clear', 'primary_color', 'footer_text', 'icp', 'hero_title', 'hero_subtitle'],
        'interact' => ['comments_enabled', 'comments_guest', 'comments_auto_approve', 'comments_notify_email', 'comments_rate_seconds'],
        'urls' => ['url_mode'],
        'uploads' => ['upload_thumb_width', 'upload_watermark_text', 'upload_watermark_position', 'watermark_image', 'upload_watermark_image_clear'],
        'mail' => ['mail_host', 'mail_port', 'mail_encryption', 'mail_username', 'mail_password', 'mail_from_address', 'mail_from_name'],
        'api' => ['cdn_url', 'api_enable', 'api_token'],
        'seo' => ['robots_extra', 'analytics_code', 'tag_autolink', 'map_default_lng', 'map_default_lat'],
    ];

    public const CHECKBOXES = ['site_closed', 'comments_enabled', 'comments_guest', 'comments_auto_approve', 'api_enable', 'tag_autolink'];

    public static function labels(): array
    {
        return array_map(fn (string $label) => WorkbenchI18n::text($label), self::LABELS);
    }

    public static function normalize(string $section): string
    {
        return $section === 'more' ? 'urls' : (array_key_exists($section, self::LABELS) ? $section : 'site');
    }
}
