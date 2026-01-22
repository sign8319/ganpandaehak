<?php
// 이 파일은 새로운 파일 생성시 반드시 포함되어야 함
if (!defined('_GNUBOARD_'))
    exit; // 개별 페이지 접근 불가

$g5_debug['php']['begin_time'] = $begin_time = get_microtime();

if (!isset($g5['title'])) {
    $g5['title'] = $config['cf_title'];
    $g5_head_title = $g5['title'];
} else {
    // 상태바에 표시될 제목
    $g5_head_title = implode(' | ', array_filter(array($g5['title'], $config['cf_title'])));
}

$g5['title'] = strip_tags($g5['title']);
$g5_head_title = strip_tags($g5_head_title);

// 현재 접속자
// 게시판 제목에 ' 포함되면 오류 발생
$g5['lo_location'] = addslashes($g5['title']);
if (!$g5['lo_location'])
    $g5['lo_location'] = addslashes(clean_xss_tags($_SERVER['REQUEST_URI']));
$g5['lo_url'] = addslashes(clean_xss_tags($_SERVER['REQUEST_URI']));
if (strstr($g5['lo_url'], '/' . G5_ADMIN_DIR . '/') || $is_admin == 'super')
    $g5['lo_url'] = '';

/*
// 만료된 페이지로 사용하시는 경우
header("Cache-Control: no-cache"); // HTTP/1.1
header("Expires: 0"); // rfc2616 - Section 14.21
header("Pragma: no-cache"); // HTTP/1.0
*/
?>
<!doctype html>
<html lang="ko">

<head>
    <meta charset="utf-8">
    <?php
    // if (G5_IS_MOBILE) {
//     echo '<meta name="viewport" id="meta_viewport" content="width=device-width,initial-scale=1.0">'.PHP_EOL;
//     echo '<meta name="HandheldFriendly" content="true">'.PHP_EOL;
//     echo '<meta name="format-detection" content="telephone=no">'.PHP_EOL;
// } else {
//     echo '<meta http-equiv="imagetoolbar" content="no">'.PHP_EOL;
//     echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">'.PHP_EOL;
// }
    echo '<meta name="viewport" id="meta_viewport" content="width=device-width,initial-scale=1.0">' . PHP_EOL;
    echo '<meta name="HandheldFriendly" content="true">' . PHP_EOL;
    echo '<meta name="format-detection" content="telephone=no">' . PHP_EOL;
    echo '<meta http-equiv="imagetoolbar" content="no">' . PHP_EOL;
    echo '<meta http-equiv="X-UA-Compatible" content="IE=edge">' . PHP_EOL;
    if ($config['cf_add_meta'])
        echo $config['cf_add_meta'] . PHP_EOL;
    ?>
    <title><?php echo $g5_head_title; ?></title>

    <!-- SEO & Open Graph -->
    <meta property="og:title" content="<?php echo $g5_head_title; ?>">
    <meta property="og:description" content="<?php echo $config['cf_title']; ?> - 전문 간판 제작 및 시공">
    <meta property="og:image" content="<?php echo G5_THEME_IMG_URL ?>/og_image.png">
    <meta property="og:url" content="<?php echo $g5['lo_url']; ?>">
    <meta property="og:type" content="website">

    <!-- Fonts (Preload for Performance) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css" />


    <!--[if lte IE 8]>
<script src="<?php echo G5_JS_URL ?>/html5.js"></script>
<![endif]-->

    <!-- jQuery 및 필수 스크립트 (GNUBoard 기본 로딩 사용) -->
    <!-- <script src="<?php echo G5_JS_URL ?>/jquery-1.12.4.min.js"></script> -->
    <!-- <script src="<?php echo G5_JS_URL ?>/jquery-migrate-1.4.1.min.js"></script> -->
    <!-- <script src="<?php echo G5_JS_URL ?>/common.js?ver=<?php echo G5_JS_VER ?>"></script> -->

    <script>
        // 자바스크립트에서 사용하는 전역변수 선언
        var g5_url = "<?php echo G5_URL ?>";
        var g5_bbs_url = "<?php echo G5_BBS_URL ?>";
        var g5_is_member = "<?php echo isset($is_member) ? $is_member : ''; ?>";
        var g5_is_admin = "<?php echo isset($is_admin) ? $is_admin : ''; ?>";
        var g5_is_mobile = "<?php echo G5_IS_MOBILE ?>";
        var g5_bo_table = "<?php echo isset($bo_table) ? $bo_table : ''; ?>";
        var g5_sca = "<?php echo isset($sca) ? $sca : ''; ?>";
        var g5_editor = "<?php echo ($config['cf_editor'] && $board['bo_use_dhtml_editor']) ? $config['cf_editor'] : ''; ?>";
        var g5_cookie_domain = "<?php echo G5_COOKIE_DOMAIN ?>";
        <?php if (defined('G5_USE_SHOP') && G5_USE_SHOP) { ?>
            var g5_theme_shop_url = "<?php echo G5_THEME_SHOP_URL; ?>";
            var g5_shop_url = "<?php echo G5_SHOP_URL; ?>";
        <?php } ?>
        <?php if (defined('G5_IS_ADMIN')) { ?>
            var g5_admin_url = "<?php echo G5_ADMIN_URL; ?>";
        <?php } ?>
    </script>
    <?php
    // [SYSTEM] 기본 스크립트 로딩 복구
    add_javascript('<script src="' . G5_JS_URL . '/jquery-1.12.4.min.js"></script>', 0);
    add_javascript('<script src="' . G5_JS_URL . '/jquery-migrate-1.4.1.min.js"></script>', 0);
    if (defined('_SHOP_')) {
        if (!G5_IS_MOBILE) {
            add_javascript('<script src="' . G5_JS_URL . '/jquery.shop.menu.js?ver=' . G5_JS_VER . '"></script>', 0);
        }
    } else {
        add_javascript('<script src="' . G5_JS_URL . '/jquery.menu.js?ver=' . G5_JS_VER . '"></script>', 0);
    }
    add_javascript('<script src="' . G5_JS_URL . '/common.js?ver=' . G5_JS_VER . '"></script>', 0);
    add_javascript('<script src="' . G5_JS_URL . '/wrest.js?ver=' . G5_JS_VER . '"></script>', 0);
    add_javascript('<script src="' . G5_JS_URL . '/placeholders.min.js"></script>', 0);


    if (G5_IS_MOBILE) {
        add_javascript('<script src="' . G5_JS_URL . '/modernizr.custom.70111.js"></script>', 1); // overflow scroll 감지
    }
    if (!defined('G5_IS_ADMIN'))
        echo $config['cf_add_script'];
    ?>

    <!-- Global Libraries (Swiper v11, GSAP) - Conditional Loading -->
    <?php
    // Swiper와 GSAP는 메인 페이지나 특정 게시판(포트폴리오, 후기 등)에서만 로드
    $is_heavy_lib_needed = defined('_INDEX_')
        || (isset($bo_table) && in_array($bo_table, array(
            'portfolio_v1',
            'review',
            'signnews',
            'portfolio_banner',
            'ca_portfolio',
            'type_portfolio',
            'beforeafter',
            'store'
        )));

    if ($is_heavy_lib_needed) {
        ?>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />
        <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/ScrollTrigger.min.js"></script>
        <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <?php } ?>
    <?php




    $shop_css = '';
    if (defined('_SHOP_'))
        $shop_css = '_shop';
    add_stylesheet('<link rel="stylesheet" href="' . run_replace('head_css_url', G5_THEME_CSS_URL . '/' . (G5_IS_MOBILE ? 'mobile' : 'default') . $shop_css . '.css?ver=' . G5_CSS_VER, G5_THEME_URL) . '">', 100);
    add_stylesheet('<link rel="stylesheet" href="' . run_replace('head_css_url', G5_THEME_CSS_URL . '/common.css?ver=' . G5_CSS_VER, G5_THEME_URL) . '">', 999);
    add_stylesheet('<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">', 0);

    ?>



    <script>
        // 모달 닫기 함수 (모든 페이지에서 사용 가능)
        function closeModal() {
            console.log('모달 닫기 함수 호출됨');

            // 직접 DOM 조작으로 모달 닫기
            const modalContainer = document.getElementById('modalContainer');
            if (modalContainer) {
                modalContainer.style.display = 'none';
                console.log('모달 닫기 성공');
            }

            console.log('모달 닫기 완료');
        }
    </script>

    <!-- Alpine.js (for Mobile Menu) -->
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

    <!-- Tailwind CSS (Global) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        window.tailwind = window.tailwind || {};
        window.tailwind.config = {
            theme: {
                screens: {
                    'sm': '375px',
                    'md': '768px',
                    'lg': '1024px',
                },
                container: {
                    center: true,
                    padding: {
                        DEFAULT: '1rem',
                        sm: '2rem',
                        lg: '4rem',
                        xl: '5rem',
                        '2xl': '6rem',
                    },
                },
                extend: {
                    colors: {
                        accent: '#A7FF4C',
                        black: '#000000',
                        white: '#FFFFFF'
                    },
                    fontFamily: {
                        pretendard: ['Pretendard', 'sans-serif']
                    },
                    fontSize: {
                        'xs': ['0.75rem', { lineHeight: '1rem' }],
                        'sm': ['0.875rem', { lineHeight: '1.25rem' }],
                        'base': ['1rem', { lineHeight: '1.5rem' }],
                        'lg': ['1.125rem', { lineHeight: '1.75rem' }],
                        'xl': ['1.25rem', { lineHeight: '1.75rem' }],
                        '2xl': ['1.5rem', { lineHeight: '2rem' }],
                        '3xl': ['1.875rem', { lineHeight: '2.25rem' }],
                        '4xl': ['2.25rem', { lineHeight: '2.5rem' }],
                        '5xl': ['3rem', { lineHeight: '1' }],
                        '6xl': ['3.75rem', { lineHeight: '1' }],
                    }
                }
            }
        }
    </script>


</head>

<body <?php echo isset($g5['body_script']) ? $g5['body_script'] : ''; ?>>