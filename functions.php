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


function atsuatsume_copy(){
    return '
    <!--?xml version="1.0" encoding="UTF-8"?--><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 661.45 193.98" width="661.4500122070312" height="193.97999572753906"><g style="isolation:isolate;"><g id="b"><g id="c"><line x1="345.68" y1=".27" x2="272.3" y2="121.91" style="fill: none; stroke: rgb(0, 0, 0); stroke-miterlimit: 10; stroke-width: 1.06px;" class="svg-elem-1"></line><path d="m116.91,60.02h8.31l7.45-.16.38.38-.16,4.67v4.29l.16,5.36-.38.38-6.92-.16h-8.85v2.63h14.21l7.72-.16.32.27v5.95l-.32.27-7.72-.16h-14.16l.21,7.72h-7.99l.21-7.72h-13.08l-7.67.16-.38-.32v-5.84l.32-.32,7.72.16h13.14v-2.63h-6.97l-7.51.16-.38-.38.16-5.09v-4.83l-.16-4.4.38-.38,7.77.16h6.7v-2.41h-.91l-5.9.16-.38-.38.16-6.38v-1.39h-5.79l-7.4.16-.38-.32v-5.79l.32-.32,7.45.16h5.79v-3.32l.38-.38h6.43l.43.32v3.38h8.1v-3.27l.38-.38h6.65l.43.32v3.32h5.68l7.45-.16.32.27v5.9l-.32.27-7.45-.16h-5.68v1.13l.16,6.65-.48.38-5.79-.16h-2.2v2.41Zm-14.69,5.58v3.59h7.35v-3.59h-7.35Zm7.45-13.62h8.1v-2.68h-8.1v2.68Zm7.13,17.21h8.42v-3.59h-8.42v3.59Z" style="fill: none; stroke: rgb(0, 0, 0); stroke-linejoin: bevel; stroke-width: 1px;" class="svg-elem-2"></path><path d="m182.11,60.56l-.32.27-6.01-.16h-10.62l-5.95.16-.38-.32v-2.52c-2.2,1.55-4.56,3-7.08,4.34-.54.27-.97.48-1.29.48s-.59-.11-.86-.38l-5.25-4.72c9.38-4.4,17.05-10.4,22.36-18.12l.59-.16,7.67,1.77.16.54c-.21.32-.43.64-.7.97,6.22,5.25,11.9,9.6,22.57,13.67l-5.04,5.15c-.59.59-1.02.86-1.5.86-.27,0-.59-.11-.97-.32-2.63-1.39-5.09-2.9-7.4-4.4v2.9Zm-26.38,22.15v4.07h-7.13l.27-8.95v-5.63l-.16-7.99.38-.38,6.38.16h6.33l6.38-.16.38.38-.16,7.67v4.45l.27,8.95h-7.13v-2.57h-5.79Zm5.79-12.82h-5.79v6.81h5.79v-6.81Zm16.19-15.39c-2.68-1.98-5.15-4.24-7.51-6.6-2.15,2.36-4.34,4.56-6.81,6.6l1.77.05h10.62l1.93-.05Zm14.26,25.79c0,5.25-2.9,6.54-8.85,6.54-.8,0-.96-.11-1.13-.59l-2.09-6.38c1.66,0,2.95-.05,3.65-.16.7-.11.91-.27.91-1.13v-8.53h-6.01v9.97l.27,11.15h-7.56l.27-11.15v-9.01l-.16-6.81.38-.38,6.86.16h6.11l6.86-.16.38.38-.16,6.43v3.38l.27,6.27Z" style="fill: none; stroke: rgb(0, 0, 0); stroke-linejoin: bevel; stroke-width: 1px;" class="svg-elem-3"></path><path d="m236.91,58.9c.48,2.41,1.13,4.83,1.93,7.13,2.31-1.82,4.61-3.91,6.38-6.01h.59l4.99,4.67v.54c-3,2.52-6.49,4.99-9.33,6.65,2.41,4.18,5.84,7.61,11.8,11.47l-5.31,5.15c-.32.32-.54.43-.8.43-.21,0-.48-.11-.8-.38-5.79-4.4-9.12-8.95-11.64-14.37-.43-.91-.7-1.5-.91-1.98.43,1.82.86,3.91.86,6.76,0,9.87-2.57,12.23-10.94,12.23-1.07,0-1.23-.16-1.55-.91l-2.52-5.47c-3.81,2.04-7.99,3.7-12.44,4.93-.27.05-.48.11-.7.11-.32,0-.59-.16-.75-.43l-3.59-5.36c9.76-2.31,18.82-6.7,24.45-11.63-.16-.54-.38-1.02-.59-1.5-5.52,3.91-11.31,6.97-18.12,9.12-.27.11-.48.11-.7.11-.32,0-.59-.16-.75-.43l-3.7-5.31c7.83-1.72,14.8-4.88,20.11-8.47-.32-.43-.64-.8-1.02-1.13-4.34,2.36-9.17,4.34-14.21,5.84-.27.11-.54.11-.7.11-.32,0-.59-.11-.8-.48l-3.54-5.09c6.65-1.39,13.08-3.75,18.02-6.33h-.97l-7.02.16-.38-.32v-5.52l.32-.32,7.08.16h15.71l6.76-.16.32.27v5.63l-.32.27-5.2-.11Zm-13.78-18.66l.38-.38h7.19l.38.38v4.13h10.88l8.85-.16.43.38-.16,4.08v1.45l.27,6.86h-7.72v-6.6h-32.55v6.6h-7.51l.27-6.86v-.97l-.16-4.56.38-.38,8.9.16h10.19v-4.13Zm1.77,44.24c1.5-.16,2.52-.7,2.68-4.88-2.36,1.88-4.88,3.54-7.56,4.99,1.66.05,3.97,0,4.88-.11Zm2.09-22.9c2.47,2.36,4.4,4.99,5.52,7.24-.8-2.57-1.72-6.06-2.31-9.65-1.02.86-2.09,1.66-3.22,2.41Z" style="fill: none; stroke: rgb(0, 0, 0); stroke-linejoin: bevel; stroke-width: 1px;" class="svg-elem-4"></path><path d="m404.85,59.33c.16.16.38.48.38.97,0,.21-.05.48-.16.75-2.47,7.13-5.95,13.03-10.35,17.69,4.24,2.25,9.33,3.75,15.44,4.83l-4.02,6.65c-.38.59-.86.86-1.66.64-6.17-1.66-11.37-3.81-15.82-6.86-4.34,3.11-9.33,5.47-14.8,7.24-.16.05-.32.11-.48.11-.43,0-.8-.27-1.18-.8l-3.54-5.15c-.97,1.82-2.15,3.65-3.49,5.36-.16.27-.32.38-.48.38-.11,0-.21-.05-.38-.21l-5.58-4.88c5.09-7.02,7.08-13.94,7.08-23.81v-10.99l-.16-8.74.38-.38,8.9.16h24.29l8.9-.16.38.38v6.27l-.38.38-8.9-.16h-25.79v7.35h23.16l4.83-.27,3.43,3.27Zm-23.75,4.67c2.04,4.02,4.45,7.35,7.45,10.19,3.06-3.27,5.42-7.13,7.13-11.15h-22.25c0,7.56-1.07,15.17-4.56,21.98,4.83-1.5,9.6-3.27,13.89-6.06-3-3.11-5.63-6.81-8.04-11.47v-.59l5.79-2.9h.59Z" style="fill: none; stroke: rgb(0, 0, 0); stroke-linejoin: bevel; stroke-width: 1px;" class="svg-elem-5"></path><path d="m464.96,89.57c-.21.54-.38.54-.97.54-4.45.16-8.95.27-13.08.27-4.72,0-9.06-.11-12.44-.38-4.66-.32-9.01-1.39-12.17-5.36-2.04,1.66-4.13,3.65-6.43,5.68l-.48-.21-3.81-6.27.16-.48c2.63-1.66,4.67-2.84,7.02-4.18v-11.53h-1.07l-4.77.16-.38-.32v-6.11l.32-.32,4.83.16h1.02l7.02-.16.38.38-.16,8.04v9.76c1.61,2.84,4.93,3.7,7.99,4.08l-4.07-4.4c4.5-1.02,7.94-2.31,9.76-4.83.54-.7.86-1.5,1.07-2.47h-3.81v2.31h-7.19l.21-10.03v-6.81l.48-.48h5.95l.54.43v8.69h4.29v-10.88h-5.74l-7.56.16-.38-.32v-5.74l.32-.32,7.61.16h1.13c-.91-2.14-1.98-4.08-3.22-5.95l.16-.43,5.25-2.14.43.16c1.56,1.98,2.95,4.24,4.13,6.86l-.16.48-2.2,1.02h5.25c1.39-2.73,2.73-6.11,3.65-8.58l.54-.21,6.54,2.52.11.54c-1.07,1.93-2.31,3.86-3.54,5.74h1.61l7.29-.16.32.27v5.84l-.32.27-7.29-.16h-6.54v6.38c0,1.66,0,3.16-.05,4.5h4.4v-8.69l.38-.43h6.38l.43.38v6.81l.27,10.35h-7.45v-2.52h-4.72c-.32,2.47-.91,4.29-1.98,5.9-1.88,2.79-4.72,4.83-8.47,6,6.7.21,17.32-.21,26.22-.91l-3,6.97Zm-39.09-36.03h-.75c-2.25-2.95-4.4-5.15-7.61-7.61l.05-.43,4.61-3.91.54-.11c3.06,2.04,5.47,4.29,8.2,7.13l-.05.59-4.99,4.34Z" style="fill: none; stroke: rgb(0, 0, 0); stroke-linejoin: bevel; stroke-width: 1px;" class="svg-elem-6"></path><path d="m506.73,50.32c2.36-2.63,4.5-5.52,6.43-8.58l.54-.11,6.22,3.27.11.59c-2.73,3.75-5.68,7.29-8.95,10.56h4.02l8.2-.16.38.38v5.79l-.38.38-8.2-.16h-10.46c-1.72,1.29-3.43,2.52-5.2,3.7h9.44l7.4-.16.38.38-.16,6.22v6.59l.27,11.9h-7.77v-2.09h-15.55v2.41h-7.56l.27-12.44v-4.4c-2.57,1.34-5.15,2.63-7.72,3.81-.32.16-.64.27-.86.27-.32,0-.64-.16-.97-.54l-4.24-4.99c7.35-3,14.53-6.49,21.02-10.67h-11.9l-7.61.16-.43-.38v-5.9l.54-.27,7.51.16h9.17v-3.97h-4.72l-7.08.16-.43-.38v-5.84l.54-.27,6.97.16h4.72v-5.36l.38-.48h6.86l.43.43v5.42h.38l7.67-.16.38.38v4.18Zm2.25,21.45h-15.55v2.68h15.55v-2.68Zm0,8.36h-15.55v2.84h15.55v-2.84Zm-10.29-28.04h-.38v3.97h2.68c1.39-1.23,2.73-2.52,4.02-3.86l-6.33-.11Z" style="fill: none; stroke: rgb(0, 0, 0); stroke-linejoin: bevel; stroke-width: 1px;" class="svg-elem-7"></path><path d="m553.65,82.17c11.53-1.93,16.73-7.45,16.73-13.89,0-8.15-4.5-13.3-9.97-14.42-1.07,10.13-4.56,19.57-8.26,24.99-2.9,4.24-5.9,5.79-10.08,5.79-3.22,0-5.52-1.55-7.56-4.77-1.55-2.41-2.63-5.36-2.63-9.71,0-12.98,9.71-23.49,24.45-23.49s22.47,9.81,22.47,21.72c0,9.76-6.86,17.16-18.23,19.84-.75.05-1.13.05-1.72-.54l-5.2-5.52Zm-14.37-12.01c0,1.77.32,3.06.91,4.07.59,1.07,1.34,1.77,2.2,1.77,1.23,0,2.79-1.23,4.24-4.13,3-6.01,4.5-11.69,5.31-18.23-7.83,1.34-12.65,8.26-12.65,16.52Z" style="fill: none; stroke: rgb(0, 0, 0); stroke-linejoin: bevel; stroke-width: 1px;" class="svg-elem-8"></path><path d="m17.54,160.93l.21.59c-1.23,6.54-2.79,12.33-5.25,18.02l-.54.16-6.43-2.9-.11-.54c2.25-4.5,4.29-10.83,5.31-17.37l.48-.27,6.33,2.3Zm10.46,17.37c0,1.55.38,1.93,3.75,1.93,3.7,0,5.04-.16,6.11-1.02.97-.8,1.39-2.36,1.72-6.7l6.76,2.73c.59.27.64.43.59,1.07-.59,5.09-1.45,7.45-2.95,8.74-1.82,1.61-3.91,2.31-11.96,2.31-10.35,0-11.9-1.29-11.9-5.79l.11-10.78v-17.53l.38-.43h6.97l.43.48v24.99Zm2.04-38.98l.54-.11c3.16,2.15,6.22,4.61,9.01,7.72l-.11.54-5.52,4.83-.48-.05c-2.68-3.49-5.68-5.95-8.63-8.31v-.54l5.2-4.08Zm16.3,15.01l.59.05c4.45,5.47,7.56,10.4,10.19,16.14l-.16.54-6.22,3.91-.54-.11c-2.73-6-5.47-10.99-9.49-16.41l.11-.54,5.52-3.59Z" style="fill: none; stroke: rgb(0, 0, 0); stroke-linejoin: bevel; stroke-width: 1px;" class="svg-elem-9"></path><path d="m86.18,180.34c11.53-1.93,16.73-7.45,16.73-13.89,0-8.15-4.5-13.3-9.97-14.42-1.07,10.13-4.56,19.57-8.26,24.99-2.9,4.24-5.9,5.79-10.08,5.79-3.22,0-5.52-1.55-7.56-4.77-1.55-2.41-2.63-5.36-2.63-9.71,0-12.98,9.71-23.49,24.45-23.49s22.47,9.81,22.47,21.72c0,9.76-6.86,17.16-18.23,19.84-.75.05-1.13.05-1.72-.54l-5.2-5.52Zm-14.37-12.01c0,1.77.32,3.06.91,4.07.59,1.07,1.34,1.77,2.2,1.77,1.23,0,2.79-1.23,4.24-4.13,3-6.01,4.5-11.69,5.31-18.23-7.83,1.34-12.65,8.26-12.65,16.52Z" style="fill: none; stroke: rgb(0, 0, 0); stroke-linejoin: bevel; stroke-width: 1px;" class="svg-elem-10"></path><path d="m149.08,163.61c-.21,2.2-.59,4.34-1.18,6.49,4.29,6.92,11.31,11.26,23.22,12.12l-3.91,6.06c-.48.75-.86.96-1.34.96-.21,0-.48-.05-.8-.11-8.69-1.23-15.44-5.15-20.38-11.74-3.86,6.65-11.21,10.62-19.79,11.9-1.07.16-1.77.11-2.25-.59l-3.81-5.74c9.38-.91,17-4.08,20.11-10.94,1.18-2.63,2.04-6,2.31-9.76l.59-.48,6.7,1.29.54.54Zm.54-23.54c-.38,3.7-1.13,7.13-2.41,10.35,6.97,2.41,14.37,5.9,21.93,9.81l.05.54-3.7,6.27-.54.11c-6.86-4.18-14.48-8.04-21.18-10.67-.16.21-.38.48-.59.75-4.13,4.77-11.31,7.51-17.75,8.53-1.07.16-1.77.11-2.25-.59l-3.81-5.47c7.02-.7,13.99-2.79,17.64-7.08,2.73-3.16,4.29-7.56,4.72-13.35l.59-.48,6.76.75.54.54Zm-14.21,30.29v.64c-2.15,3.06-4.34,5.31-6.65,7.13h-.54l-4.77-3.86v-.54c2.2-1.66,4.34-3.7,6.49-6.97h.59l4.88,3.59Zm.54-24.08v.64c-2.14,3.06-4.34,5.31-6.65,7.13h-.54l-4.77-3.86v-.54c2.2-1.66,4.34-3.7,6.49-6.97h.59l4.88,3.59Zm29.22,24.13c-2.2,2.41-4.18,4.5-6.6,6.43-.21.16-.38.21-.48.21-.16,0-.32-.05-.54-.27l-4.4-3.65c2.14-2.2,4.18-4.83,5.68-7.18l.48-.05,5.84,3.97v.54Zm.27-23c-2.2,2.41-4.18,4.5-6.6,6.43-.21.16-.38.21-.48.21-.16,0-.32-.05-.54-.27l-4.4-3.65c2.14-2.2,4.18-4.83,5.68-7.18l.48-.05,5.84,3.97v.54Z" style="fill: none; stroke: rgb(0, 0, 0); stroke-linejoin: bevel; stroke-width: 1px;" class="svg-elem-11"></path><path d="m178.09,168.06c6.01-4.88,10.24-9.01,13.35-13.67-2.57.21-5.25.27-7.77.27l-.32-.32-.21-6.59.38-.32c4.02,0,7.99-.11,11.96-.38.96-2.2,1.77-4.56,2.52-7.19l.43-.27,7.35,1.07.32.38c-.64,1.88-1.29,3.49-1.88,5.04,2.68-.43,5.36-.91,8.04-1.55l.48.27.54,6.33-.32.48c-3.91.8-8.31,1.5-12.76,2.04-.86,1.29-2.04,2.95-3.11,4.24,1.61-.48,2.9-.7,5.04-.7,3.32,0,5.74,2.09,6.54,5.15,3.38-1.29,7.19-2.63,11.37-4.02l.54.21,3.32,6.59-.27.48c-5.42,1.39-10.03,2.79-13.99,4.24.11,2.09.16,4.72.21,7.29l-.38.32-7.08.27-.43-.32v-4.4c-3.27,1.56-5.09,3.06-5.09,4.88s2.14,2.31,6.92,2.36c3.91.05,9.28-.43,13.99-1.13l.54.32.48,7.13-.32.43c-5.09.43-9.6.7-15.87.7-10.83,0-13.67-4.56-13.67-9.17,0-5.15,4.34-8.74,9.33-11.58.97-.54,2.04-1.07,3.22-1.61-.27-1.34-1.07-2.04-2.68-2.04-2.15,0-4.24.54-6.11,1.88-2.36,1.72-6.01,5.15-9.33,8.69l-.54.05-4.72-5.25v-.59Z" style="fill: none; stroke: rgb(0, 0, 0); stroke-linejoin: bevel; stroke-width: 1px;" class="svg-elem-12"></path><path d="m240.56,163.34l-.64.16c-2.52-2.04-5.52-3.97-7.67-5.09l-.16-.43,4.18-5.09.64-.16c2.04.86,5.25,2.79,7.72,4.5l.16.54-4.24,5.58Zm-7.24,21.72c2.52-5.15,4.24-10.03,6.43-18.12l5.74,3.54c.54.32.59.75.48,1.29-1.5,5.9-2.84,9.97-5.58,16.73l-.54.32-6.33-3.22-.21-.54Zm9.44-34.85l-.64.11c-2.47-2.2-5.31-4.13-7.83-5.42l-.11-.43,4.56-4.88.64-.11c2.57,1.23,5.36,2.95,7.88,4.83l.11.54-4.61,5.36Zm11.47-10.24l.54.21c2.31,2.84,4.18,5.95,5.84,9.49l-.16.48-5.9,2.79-.43-.11c-1.34-3.38-3.16-6.86-5.31-9.87l.16-.48,5.25-2.52Zm3.65,37.53v11.96h-7.51l.27-13.19v-15.39l-.16-6.92.43-.38,7.13.16h3.43v-15.17l.38-.43h6.7l.43.38v15.23h3.43l7.08-.16.38.38-.16,6.92v12.12l.27,9.81c0,5.25-3.38,6.54-9.87,6.54-.86,0-1.02-.11-1.18-.59l-2.31-6.38c1.66,0,3.27-.05,4.72-.16.75-.05.97-.27.97-1.13v-3.59h-14.42Zm14.42-17.75h-14.42v3.11h14.42v-3.11Zm0,8.85h-14.42v3.16h14.42v-3.16Zm9.22-26.38l.27.48c-1.77,3.54-3.81,7.08-5.84,9.87-.27.38-.48.54-.7.54s-.43-.11-.75-.27l-4.93-2.52c2.09-3.43,4.13-7.4,5.31-10.56l.43-.21,6.22,2.68Z" style="fill: none; stroke: rgb(0, 0, 0); stroke-linejoin: bevel; stroke-width: 1px;" class="svg-elem-13"></path><path d="m315.84,167.21c2.31-.21,3.54-.43,4.4-.59,1.13-.21,1.5-.48.96-1.56-1.02-1.98-2.41-4.34-4.07-6.97-5.79,1.45-12.06,2.2-19.04,2.41l-.43-.38-1.23-6.43.32-.38c5.74-.27,10.99-.86,15.92-1.98-1.93-2.68-4.08-5.52-6.43-8.42l.11-.48,6.92-2.57.54.05c2.25,2.84,4.56,6.01,6.81,9.22,3.22-1.13,6.27-2.47,9.22-4.08l.54.11,3.27,6.54-.21.48c-2.79,1.34-5.58,2.52-8.42,3.54,2.31,3.65,4.34,7.02,5.74,9.6.64,1.18.97,2.36.97,3.65,0,2.41-2.41,3.81-4.56,3.81-1.02,0-1.88-.32-2.79-.86-1.88-1.07-3.32-1.77-8.42-2.95l-.11-1.77Zm-19.14,7.72l5.74-3.59.48.11c3,5.42,10.08,6.97,17.27,6.97,1.88,0,3.7-.11,6.43-.32l.27.38-1.77,7.99-.48.27c-1.82.11-3.27.11-5.42.11-10.62,0-18.55-3.91-22.57-11.42l.05-.48Z" style="fill: none; stroke: rgb(0, 0, 0); stroke-linejoin: bevel; stroke-width: 1px;" class="svg-elem-14"></path><path d="m372.79,141.04c-.48,2.15-1.07,4.24-1.56,6.17,1.88-.38,3.75-.8,5.63-1.29l.48.21,1.02,4.72c6.01.48,11.69,1.72,16.57,4.18l.16.54-2.2,5.95-.43.21c-1.72-.8-2.79-.91-4.34-.91-3.43,0-6.38,1.34-6.38,3.75,0,1.66.8,4.13,2.36,7.99,4.45,1.93,8.69,4.4,11.69,6.86v.54l-4.5,5.79-.48.05c-2.31-2.09-4.02-3.59-6.27-5.09-.16,4.34-4.88,7.18-10.94,7.18-6.97,0-11.26-2.79-11.26-8.36,0-5.04,4.29-8.58,10.03-8.58,1.88,0,2.36.11,3.49.27-.8-2.73-1.29-4.45-1.18-6.54.11-2.9,2.84-5.36,7.29-6.7-2.09-.38-4.72-.59-7.29-.75l-.27-.38,1.34-3.06c-2.31.48-4.66.96-7.02,1.29-3.22,8.95-7.29,16.03-12.65,23.59l-.54.11-6.33-4.08v-.48c4.5-5.42,8.15-11.63,10.83-18.12-2.79.27-5.63.43-8.58.48l-.43-.32-.16-6.6.38-.38c4.08-.11,7.88-.38,11.53-.75.86-3,1.55-5.95,2.04-8.9l.48-.32,7.19,1.23.32.48Zm-.64,36.14c-1.82,0-2.9,1.13-2.9,2.04,0,1.29,1.18,2.14,3.11,2.14,3.06,0,4.83-1.07,4.77-3.22-1.23-.7-2.57-.96-4.99-.96Z" style="fill: none; stroke: rgb(0, 0, 0); stroke-linejoin: bevel; stroke-width: 1px;" class="svg-elem-15"></path><path d="m408.5,145.76l7.56.27.38.38c-.48,5.95-.64,11.26-.64,14.37,0,4.72.48,7.88,1.23,10.62.59,2.09,1.29,3.65,2.47,3.65,1.45,0,3.27-2.9,4.5-7.83l5.36,6.76c.38.48.38.75.16,1.34-2.14,5.58-5.63,9.6-9.97,9.6-3.49,0-6.54-2.47-8.69-6.81-1.66-3.38-3.11-9.38-3.11-16.67,0-4.34.11-9.49.38-15.34l.38-.32Zm25.42,9.33l5.15-4.13.59-.11c6.01,4.08,11.1,10.13,14.21,17.32l-.16.48-6.81,4.24-.54-.16c-2.9-7.4-6.97-13.24-12.55-17.16l.11-.48Z" style="fill: none; stroke: rgb(0, 0, 0); stroke-linejoin: bevel; stroke-width: 1px;" class="svg-elem-16"></path><path d="m477.83,152.14c3.27,1.66,6.27,3.38,9.12,5.25,2.52-4.56,4.72-9.54,6.6-15.07l.43-.16,7.94,3,.16.43c-2.25,6.01-4.93,11.69-8.15,16.94,3,2.47,5.79,5.15,8.31,8.04l.05.59-5.9,5.95h-.48c-2.25-2.63-4.56-5.04-7.02-7.35-4.88,6.22-10.78,11.69-17.96,15.98-.27.16-.54.27-.75.27-.27,0-.59-.11-.97-.43l-6.17-4.56c7.72-4.08,14.1-9.6,19.3-16.57-3-2.2-6.33-4.29-10.13-6.33v-.48l5.04-5.42.59-.11Z" style="fill: none; stroke: rgb(0, 0, 0); stroke-linejoin: bevel; stroke-width: 1px;" class="svg-elem-17"></path><path d="m529.85,163.02l-8.1.16-.38-.43v-6.81l.43-.38,7.99.16h25.31l7.56-.16.43.38v6.81l-.38.43-7.61-.16h-7.45c-.38,7.24-3.32,14.32-8.63,19.36-1.82,1.72-3.86,3.06-6.06,4.34-.43.21-.64.32-.91.32s-.59-.16-1.13-.54l-6.81-4.24c8.47-3.22,14.05-9.49,14.75-19.25h-9.01Zm18.93-11.85h-13.94l-7.13.16-.38-.43v-6.92l.48-.32,6.6.16h14.32l7.29-.16.43.38v6.86l-.38.43-7.29-.16Zm7.13-9.97l3.16-1.34.54.05c1.61,2.04,3.06,4.24,4.18,6.7l-.16.48-3.32,1.45-.54-.11c-1.18-2.52-2.25-4.56-3.97-6.76l.11-.48Zm5.63-2.25l3.11-1.39.54.05c1.61,2.04,2.9,4.08,4.02,6.54l-.16.48-3.11,1.45-.54-.11c-1.18-2.52-2.25-4.34-3.97-6.6l.11-.43Z" style="fill: none; stroke: rgb(0, 0, 0); stroke-linejoin: bevel; stroke-width: 1px;" class="svg-elem-18"></path><path d="m596.82,173.37l.21,12.22h-7.99l.21-11.96v-4.67c-2.79,1.98-5.74,3.81-9.54,5.95-.8.38-.91.32-1.5-.21l-5.09-4.4c10.4-4.88,17.86-10.4,25.04-18.55l.54-.05,5.74,4.08.05.48c-2.79,2.84-5.36,5.15-7.67,7.13v9.97Z" style="fill: none; stroke: rgb(0, 0, 0); stroke-linejoin: bevel; stroke-width: 1px;" class="svg-elem-19"></path><path d="m642.02,155.62c0,.86,0,1.98-.05,2.79,2.52-1.98,5.31-4.45,7.13-6.54h-21.29l-9.28.16-.38-.43v-7.02l.48-.32,9.06.16h23.86l5.36-.27,3.75,5.04c.16.27.27.48.27.75,0,.32-.11.64-.43,1.02-2.68,3.27-7.4,8.1-11.8,12.01-.86.75-1.13.75-1.93.21l-4.93-3.33c-.54,11.53-3.59,19.14-12.81,25.95-.38.27-.64.38-.97.38s-.7-.16-1.18-.43l-7.13-3.65c11.21-6.43,13.46-13.3,13.24-26.43l.38-.43h8.26l.38.38Z" style="fill: none; stroke: rgb(0, 0, 0); stroke-linejoin: bevel; stroke-width: 1px;" class="svg-elem-20"></path><line x1="84.83" y1="66.13" x2="260.17" y2="66.13" style="fill: none; mix-blend-mode: multiply; stroke: rgb(248, 215, 73); stroke-miterlimit: 10; stroke-width: 56.69px;" class="svg-elem-21"></line><line x1="358.73" y1="66.13" x2="586.42" y2="66.13" style="fill: none; mix-blend-mode: multiply; stroke: rgb(248, 215, 73); stroke-miterlimit: 10; stroke-width: 56.69px;" class="svg-elem-22"></line><line y1="165.63" x2="660.95" y2="165.63" style="fill: none; mix-blend-mode: multiply; stroke: rgb(248, 215, 73); stroke-miterlimit: 10; stroke-width: 56.69px;" class="svg-elem-23"></line></g></g></g></svg>
    ';
  }
  add_shortcode('atsuatsume_copy','atsuatsume_copy');