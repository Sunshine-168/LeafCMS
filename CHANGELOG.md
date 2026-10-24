# 更新记录

## 2026-10-24

- 公开目录整理：`public/css`、`public/brand`、`public/themes`、`public/vendor` 收到 `public/assets` 下。安装页、后台、官网主题和 IG 主题的样式、脚本、图片都改为新路径。上传文件仍走 `public/storage`，没有另做一套上传目录。
- 站点 Logo、Favicon 和分享图：以 `/` 开头、又不是 `/storage` 的地址按原路径使用，不再被拼成 `/storage/assets/...`。
- 仓库不再包含 `tests` 和临时脚本 `storage/framework/tmp_retest.php`。本地可以继续放测试，这些文件不会进入版本库。
