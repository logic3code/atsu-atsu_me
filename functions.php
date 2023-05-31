<?php

// 子テーマのstyle.cssを後から読み込む
add_action('wp_enqueue_scripts', 'stk_add_child_stylesheet', 999);
function stk_add_child_stylesheet()
{
    $theme_ver = wp_get_theme()->Version;
    $style = get_option('side_options_style_min_css', 'normal') == 'normal' ? 'style' : 'style.min';
    $template_directory = get_stylesheet_directory();

    if (get_option('side_options_style_min_css', 'normal') === 'inline') {

        //データベースキャッシュに値を取得
        $data = get_transient('stk_child_style_css_cache');
        
        // cacheがない場合
        if ($data === false) {

            load_template(ABSPATH . 'wp-admin/includes/file.php');
            global $wp_filesystem;
            if (WP_Filesystem()) {
                $data = $wp_filesystem->get_contents("{$template_directory}/style.min.css");
            }

            //取得した値をデータベースキャッシュに保存
            set_transient('stk_child_style_css_cache', $data, 60 * 60 * 4);
        }

        // var_dump($data);

        if($data)
        {
            $handle = 'stk_child-style';
            $src = false;
            $deps = array();
            wp_register_style($handle, $src, $deps);
            wp_enqueue_style($handle);
            wp_add_inline_style($handle, $data);
        } else {
            wp_enqueue_style('stk_child-style', get_stylesheet_directory_uri() . '/' . $style . '.css', array('stk_style'), $theme_ver, 'all');
        }
    } else {
        wp_enqueue_style('stk_child-style', get_stylesheet_directory_uri() . '/' . $style . '.css', array('stk_style'), $theme_ver, 'all');
    }
}



// カスタマイズでコードを追記する場合はここよりも下に記載してください

// page top button
if (!function_exists('pagetop')) {
	add_action('wp_footer', 'pagetop');
	function pagetop()
	{
		if (get_option('advanced_pagetop_btn', 'on') == 'off') {
			return;
		}
		$amp_class = stk_is_amp() ? ' class="pt-active pt-a-amp"' : '';

		$output = '
		<div id="page-top"' . $amp_class . '>
			<a href="#container" class="pt-button" title="ページトップへ"><img src="/wp-content/uploads/2023/05/pagetop.svg" alt="PAGE TOP"></a>
		</div>';
		$output = minify_html($output);
		if (!stk_is_amp()) {
			$script = "
			<script id=\"stk-script-pt-active\">
				(function () {
					const select = document.querySelector('#stk_observer_target');
					const observer = new window.IntersectionObserver((entry) => {
						if (!entry[0].isIntersecting) {
							document.querySelector('#page-top').classList.add('pt-active');
						} else {
							document.querySelector('#page-top').classList.remove('pt-active');
						}
					});
					observer.observe(select);
				}());
			</script>";
			$output .= minify_js($script);
		}
		echo $output;
	}
}

function post_number() {
    $number = ( max( 1, $paged ) - 1 ) * $wp_query->query_vars['posts_per_page'] + $wp_query->current_post + 1;
    if(!$number)
    {
        return 'なにもないよ';
    } else {
        return '<span class="post-number gf bold">#0' . $number . '</span>';
    }
}
// 記事内のサムネイル画像
if (!function_exists('stk_post_main_thum')) {
	function stk_post_main_thum($post_id, $container_class = null)
	{
		if (!has_post_thumbnail() || get_option('post_options_eyecatch_display', 'on') == 'off') {
			return;
		}

		$figureclass = $container_class ? 'eyecatch stk_post_main_thum ' . $container_class : 'eyecatch stk_post_main_thum';

		echo '<figure class="' . $figureclass . '">';
		the_post_thumbnail(
			$post_id,
			array(
				'class' => 'stk_post_main_thum__img',
				'loading' => false,
			)
		);
		if ($pt_caption = get_post(get_post_thumbnail_id())->post_excerpt) { //caption
			echo '<figcaption class="eyecatch-caption-text">' . $pt_caption . '</figcaption>';
		}
        
        echo post_number();

		echo '</figure>';
	}
}

if (!function_exists('skt_oc_post_thum')) {
	function skt_oc_post_thum($stk_eyecatch_size = 'oc-post-thum')
	{

		$post_id = get_the_ID();
		$image_id = get_post_thumbnail_id($post_id);

		if ($image_id) {

			$thumb = get_the_post_thumbnail(
				$post_id,
				$stk_eyecatch_size,
				array(
					'class' => 'archives-eyecatch-image attachment-' . $stk_eyecatch_size,
				)
			);

            $thumb .= post_number();
		} else {
			$thumb = oc_noimg();
		}
		$thumb = str_replace('100vw', '45vw', $thumb);
		return $thumb;
	}
}