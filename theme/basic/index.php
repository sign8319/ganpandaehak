<?php
if (!defined('_INDEX_'))
    define('_INDEX_', true);
if (!defined('_GNUBOARD_'))
    exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH . '/index.php');
    return;
}

if (G5_COMMUNITY_USE === false) {
    include_once(G5_THEME_SHOP_PATH . '/index.php');
    return;
}

include_once('./_common.php');
include_once(G5_THEME_PATH . '/head.php');

$mb_name = isset($member['mb_name']) ? $member['mb_name'] : '';
$mb_email = isset($member['mb_email']) ? $member['mb_email'] : '';
$mb_hp = isset($member['mb_hp']) ? $member['mb_hp'] : '';
?>



<style>
    /* ============================================== 
     폰트 및 기본 설정
     ============================================== */
    /* Fonts Moved to head.sub.php or common.css */

    * {
        font-family: 'Pretendard', 'Noto Sans KR', sans-serif;
    }

    /* ============================================== 
     트렌디한 메뉴바 스타일
     ============================================== */
    #hd,
    #header,
    header,
    .header,
    .gnb_wrap {
        position: fixed !important;
        top: 0 !important;
        left: 0 !important;
        width: 100% !important;
        z-index: 9999 !important;
        background: rgba(255, 255, 255, 0.95) !important;
        backdrop-filter: blur(10px) !important;
        border-bottom: 1px solid rgba(249, 115, 22, 0.1) !important;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08) !important;
        transition: all 0.3s ease !important;
    }

    body {
        padding-top: 100px !important;
    }

    /* 메뉴 링크 스타일 */
    #hd nav a,
    #header nav a,
    .gnb_wrap a,
    .header nav a {
        color: #374151 !important;
        font-weight: 600 !important;
        font-size: 15px !important;
        padding: 12px 20px !important;
        margin: 0 4px !important;
        border-radius: 12px !important;
        transition: all 0.3s ease !important;
        position: relative !important;
        display: inline-block !important;
    }

    /* 메뉴 호버 효과 */
    #hd nav a:hover,
    #header nav a:hover,
    .gnb_wrap a:hover,
    .header nav a:hover {
        background: linear-gradient(135deg, #f97316 0%, #fb923c 100%) !important;
        color: white !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3) !important;
    }

    /* 활성 메뉴 */
    #hd nav a.active,
    #header nav a.active,
    .gnb_wrap a.active,
    .header nav a.active {
        background: linear-gradient(135deg, #f97316 0%, #fb923c 100%) !important;
        color: white !important;
    }

    /* 로고 스타일 */
    #hd .logo,
    #header .logo,
    .header .logo,
    #logo,
    .logo {
        font-size: 24px !important;
        font-weight: 900 !important;
        background: linear-gradient(135deg, #f97316 0%, #fb923c 100%) !important;
        -webkit-background-clip: text !important;
        -webkit-text-fill-color: transparent !important;
        background-clip: text !important;
        transition: all 0.3s ease !important;
    }

    #hd .logo:hover,
    #header .logo:hover,
    .header .logo:hover,
    #logo:hover,
    .logo:hover {
        transform: scale(1.05) !important;
    }

    /* 상단 알림 배너 스타일 */
    #hd .top-banner,
    #header .top-banner {
        background: linear-gradient(90deg, #fef3c7 0%, #fde68a 100%) !important;
        padding: 8px 0 !important;
        text-align: center !important;
        font-size: 13px !important;
        font-weight: 600 !important;
        color: #92400e !important;
    }

    /* 로그인/회원가입 버튼 */
    #hd .btn-login,
    #header .btn-login {
        background: white !important;
        border: 2px solid #f97316 !important;
        color: #f97316 !important;
        padding: 8px 20px !important;
        border-radius: 20px !important;
        font-weight: 600 !important;
        transition: all 0.3s ease !important;
    }

    #hd .btn-login:hover,
    #header .btn-login:hover {
        background: #f97316 !important;
        color: white !important;
        transform: translateY(-2px) !important;
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.3) !important;
    }


    /* 위로 가기 버튼 숨기기 */
    #top_btn,
    .top_btn,
    #movetop,
    .to_top {
        display: none !important;
    }

    /* ============================================== 
     애니메이션 정의
     ============================================== */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes fadeInRight {
        from {
            opacity: 0;
            transform: translateX(30px);
        }

        to {
            opacity: 1;
            transform: translateX(0);
        }
    }

    @keyframes bounce-custom {

        0%,
        100% {
            transform: translateY(-25%);
        }

        50% {
            transform: translateY(0);
        }
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0px);
        }

        50% {
            transform: translateY(-20px);
        }
    }

    @keyframes shimmer {
        0% {
            background-position: -1000px 0;
        }

        100% {
            background-position: 1000px 0;
        }
    }

    @keyframes slideMarquee {
        0% {
            transform: translateX(0);
        }

        100% {
            transform: translateX(-50%);
        }
    }

    @keyframes pulse-glow {

        0%,
        100% {
            box-shadow: 0 0 20px rgba(249, 115, 22, 0.4);
        }

        50% {
            box-shadow: 0 0 40px rgba(249, 115, 22, 0.8);
        }
    }

    .fade-in-up {
        opacity: 0;
        animation: fadeInUp 1s ease-out forwards;
    }

    .fade-in-right {
        opacity: 0;
        animation: fadeInRight 1s ease-out 0.3s forwards;
    }

    .animate-bounce-custom {
        animation: bounce-custom 1s infinite;
    }

    .animate-float {
        animation: float 3s ease-in-out infinite;
    }

    .shimmer-bg {
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        background-size: 1000px 100%;
        animation: shimmer 3s infinite;
    }

    .animate-pulse-glow {
        animation: pulse-glow 2s ease-in-out infinite;
    }

    /* ============================================== 
     카드 호버 효과
     ============================================== */
    .card-hover {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        pointer-events: auto;
    }

    .card-hover:hover {
        transform: translateY(-12px) scale(1.02);
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
    }

    /* 이미지 확대 효과 */
    .card-image-container {
        overflow: hidden;
        position: relative;
        pointer-events: none;
    }

    .card-image {
        transition: transform 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        pointer-events: none;
    }

    .card-hover:hover .card-image {
        transform: scale(1.15);
    }

    /* 그라데이션 오버레이 */
    .gradient-overlay {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: linear-gradient(180deg, transparent 0%, rgba(0, 0, 0, 0.7) 100%);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .card-hover:hover .gradient-overlay {
        opacity: 1;
    }

    /* ============================================== 
     마키 애니메이션 (실시간 알림)
     ============================================== */
    .marquee {
        display: flex;
        overflow: hidden;
        user-select: none;
        gap: 2rem;
    }

    .marquee-content {
        display: flex;
        animation: slideMarquee 30s linear infinite;
        gap: 2rem;
    }

    .marquee:hover .marquee-content {
        animation-play-state: paused;
    }

    /* ============================================== 
     퀴즈 진행 바
     ============================================== */
    .progress-bar {
        height: 6px;
        background: #e5e7eb;
        border-radius: 999px;
        overflow: hidden;
    }

    .progress-fill {
        height: 100%;
        background: linear-gradient(90deg, #f97316, #fb923c);
        transition: width 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }

    /* ============================================== 
     버튼 스타일
     ============================================== */
    .btn-primary {
        background: linear-gradient(135deg, #f97316 0%, #fb923c 100%);
        box-shadow: 0 10px 25px -5px rgba(249, 115, 22, 0.4);
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 35px -5px rgba(249, 115, 22, 0.6);
    }

    .btn-primary:active {
        transform: translateY(0);
    }

    /* 글래스모피즘 효과 */
    .glass-effect {
        background: rgba(255, 255, 255, 0.1);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(255, 255, 255, 0.2);
    }

    /* ============================================== 
     반응형 숨김/표시
     ============================================== */
    @media (max-width: 768px) {
        .desktop-only {
            display: none !important;
        }
    }

    @media (min-width: 769px) {
        .mobile-only {
            display: none !important;
        }
    }

    /* ============================================== 
     Swiper 슬라이더 스타일
     ============================================== */
    .review-swiper,
    .news-swiper {
        padding: 20px 0 60px 0;
    }

    .review-swiper .swiper-slide,
    .news-swiper .swiper-slide {
        height: auto;
    }

    .swiper-button-next,
    .swiper-button-prev {
        color: #f97316;
        background: white;
        width: 44px;
        height: 44px;
        border-radius: 50%;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        transition: all 0.3s ease;
    }

    .swiper-button-next::after,
    .swiper-button-prev::after {
        font-size: 18px;
        font-weight: bold;
    }

    .swiper-button-next:hover,
    .swiper-button-prev:hover {
        background: #f97316;
        color: white;
        transform: scale(1.1);
    }

    .swiper-pagination {
        bottom: 20px !important;
    }

    .swiper-pagination-bullet {
        background: #d1d5db;
        opacity: 0.5;
        width: 10px;
        height: 10px;
    }

    .swiper-pagination-bullet-active {
        background: #f97316;
        opacity: 1;
        width: 24px;
        border-radius: 5px;
    }

    /* Scroll Down Indicator Animation */
    @keyframes scroll-wheel {
        0% {
            opacity: 1;
            transform: translateY(0);
        }

        100% {
            opacity: 0;
            transform: translateY(15px);
        }
    }

    .animate-scroll-wheel {
        animation: scroll-wheel 2s cubic-bezier(0.15, 0.41, 0.69, 0.94) infinite;
    }
</style>

<!-- ==============================================
     [2] 메인 히어로 섹션
     ============================================== -->
<div class="relative w-full overflow-hidden bg-gradient-to-br from-orange-50 via-white to-orange-50">
    <section
        class="relative w-full pt-28 pb-8 md:pt-40 md:pb-32 px-4 md:px-12 flex flex-col md:flex-row items-center justify-between max-w-7xl mx-auto">
        <!-- 배경 장식 -->
        <div class="absolute top-20 right-0 w-1/2 h-full bg-orange-100 rounded-l-[100px] -z-10 blur-3xl opacity-40">
        </div>
        <div class="absolute bottom-20 left-0 w-1/3 h-1/3 bg-yellow-100 rounded-r-[100px] -z-10 blur-3xl opacity-30">
        </div>

        <!-- 왼쪽: 텍스트 영역 -->
        <div class="flex-1 md:pr-10 z-10 fade-in-up w-full text-center md:text-left">
            <div
                class="inline-block px-3 py-1 mb-5 md:mb-8 bg-gradient-to-r from-orange-500 to-orange-400 text-white rounded-full text-xs md:text-sm font-bold tracking-wide shadow-lg">
                🏆 고객 만족도 1위 간판 전문기업
            </div>

            <!-- Sub Headlines Group -->
            <div class="mb-4">
                <h2 class="text-gray-500 text-base md:text-2xl font-medium mb-1">
                    생각을 현실로, 간판으로 완성합니다.
                </h2>
                <div class="text-2xl md:text-5xl font-bold text-gray-800">
                    성공을 디자인하는
                </div>
            </div>

            <!-- Main Title -->
            <h1 class="font-black text-gray-900 leading-none mb-6 md:mb-8">
                <div class="text-[40px] md:text-7xl tracking-tight">
                    <span class="relative inline-block whitespace-nowrap">
                        간판맛집,
                        <span
                            class="absolute bottom-2 left-0 w-full h-3 md:h-5 bg-orange-200 opacity-50 -z-10 shimmer-bg"></span>
                    </span>
                    <span class="text-orange-500 whitespace-nowrap">간판대학</span>
                    <span class="text-orange-500 inline-block animate-bounce-custom">.</span>
                </div>
            </h1>

            <!-- Description -->
            <p class="text-gray-500 text-base mb-8 max-w-lg leading-relaxed">
                사장님의 가게가 동네의 랜드마크가 되도록.<br />
                최고의 디자인과 시공 퀄리티를 약속드립니다.
            </p>

            <!-- 버튼 그룹 -->
            <div class="flex flex-row gap-2 md:gap-4 w-full md:max-w-lg mb-6 justify-center md:justify-start">
                <button onclick="openConsultModal()"
                    class="flex-1 btn-primary px-3 py-3 md:px-4 md:py-4 text-white rounded-xl font-bold text-base md:text-lg flex items-center justify-center gap-1 md:gap-2 shadow-md hover:shadow-lg transition-all">
                    무료 견적 받기
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                        stroke="currentColor" class="w-4 h-4 md:w-5 md:h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </button>
                <button onclick="openQuizModal()"
                    class="flex-1 px-3 py-3 md:px-4 md:py-4 bg-white text-gray-700 border-2 border-gray-200 rounded-xl font-bold text-base md:text-lg hover:border-orange-500 hover:text-orange-500 transition-all shadow-sm hover:shadow-md flex justify-center items-center">
                    🎯 30초 간판 추천
                </button>
            </div>
        </div>

        <!-- 오른쪽: 이미지 영역 -->
        <div class="flex-1 relative mt-4 md:mt-0 fade-in-right w-full">
            <div class="relative z-10 animate-float">
                <img src="<?php echo G5_THEME_IMG_URL ?>/main_truck.png" alt="간판대학 트럭"
                    class="w-[70%] md:w-full max-w-[600px] mx-auto drop-shadow-2xl">
            </div>
            <div
                class="absolute -bottom-8 left-1/2 -translate-x-1/2 w-[80%] h-10 bg-black opacity-15 blur-2xl rounded-[100%]">
            </div>
        </div>

        <!-- Scroll Down Indicator -->
        <div class="absolute bottom-6 md:bottom-10 left-1/2 transform -translate-x-1/2 z-20 flex flex-col items-center gap-1 cursor-pointer opacity-70 hover:opacity-100 transition-opacity duration-300"
            onclick="window.scrollTo({top: window.innerHeight - 80, behavior: 'smooth'})">
            <span
                class="text-[10px] md:text-xs font-bold text-orange-500 uppercase tracking-[0.2em] animate-pulse mb-1">Scroll</span>
            <div
                class="w-[26px] h-[42px] border-2 border-orange-400 rounded-full flex justify-center p-1 bg-white/30 backdrop-blur-sm shadow-sm mb-1">
                <div class="w-1 h-2 bg-orange-500 rounded-full animate-scroll-wheel"></div>
            </div>
            <!-- Added Down Arrow -->
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
                stroke="currentColor" class="w-5 h-5 text-orange-500 animate-bounce">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
            </svg>
        </div>
    </section>
</div>

<main>
    <!-- ==============================================
         [3] 상담 섹션 - 완전 리뉴얼 (카드 형식)
         ============================================== -->
    <section class="py-24 bg-slate-900 relative overflow-hidden">
        <div class="absolute inset-0 opacity-10"
            style="background-image: radial-gradient(#ffffff 1px, transparent 1px); background-size: 30px 30px;"></div>
        <div
            class="absolute top-0 right-0 w-96 h-96 bg-orange-500 rounded-full blur-[120px] opacity-20 pointer-events-none">
        </div>
        <div
            class="absolute bottom-0 left-0 w-80 h-80 bg-blue-500 rounded-full blur-[100px] opacity-20 pointer-events-none">
        </div>

        <div class="max-w-7xl mx-auto px-4 relative z-10">
            <div class="text-center mb-16">
                <span class="text-orange-500 font-bold tracking-wider uppercase mb-2 block">Premium Consultation</span>
                <h2 class="text-4xl md:text-5xl font-black text-white mb-6">
                    어떤 <span class="text-orange-500">간판</span>이 어울릴까요?
                </h2>
                <p class="text-xl text-gray-400 max-w-2xl mx-auto">
                    고민은 저희가 하겠습니다. 사장님은 사업에만 집중하세요.<br>
                    전문가가 1:1 맞춤 상담으로 최적의 솔루션을 제안합니다.
                </p>
            </div>

            <div class="grid grid-cols-2 gap-3 md:gap-8 max-w-5xl mx-auto">
                <div
                    class="group relative bg-white rounded-2xl md:rounded-3xl p-0.5 md:p-1 shadow-2xl hover:-translate-y-2 transition-all duration-300">
                    <div
                        class="absolute inset-0 bg-gradient-to-r from-orange-500 to-orange-400 rounded-2xl md:rounded-3xl transform rotate-1 group-hover:rotate-2 transition-transform opacity-70">
                    </div>
                    <div
                        class="relative bg-white h-full rounded-[14px] md:rounded-[20px] p-4 md:p-10 flex flex-col overflow-hidden">
                        <div
                            class="absolute top-0 right-0 w-20 h-20 md:w-32 md:h-32 bg-orange-100 rounded-bl-full -mr-6 -mt-6 md:-mr-10 md:-mt-10 transition-transform group-hover:scale-110">
                        </div>

                        <div
                            class="w-10 h-10 md:w-16 md:h-16 bg-orange-100 text-orange-600 rounded-xl md:rounded-2xl flex items-center justify-center mb-3 md:mb-6 text-xl md:text-3xl">
                            📋
                        </div>

                        <h3
                            class="text-lg md:text-3xl font-bold text-gray-900 mb-2 md:mb-3 group-hover:text-orange-600 transition-colors">
                            전문가 방문
                        </h3>
                        <p class="text-gray-600 mb-4 md:mb-8 leading-relaxed text-xs md:text-lg">
                            현장 실측부터 견적까지<br class="hidden md:block">
                            한 번에 해결하세요.
                        </p>

                        <ul class="space-y-2 md:space-y-4 mb-4 md:mb-10 flex-1 hidden md:block">
                            <li class="flex items-center gap-2 md:gap-3 text-gray-700 font-medium text-xs md:text-base">
                                <span
                                    class="w-4 h-4 md:w-6 md:h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-[10px] md:text-xs">✓</span>
                                요청 후 30분 내 해피콜
                            </li>
                            <li class="flex items-center gap-2 md:gap-3 text-gray-700 font-medium text-xs md:text-base">
                                <span
                                    class="w-4 h-4 md:w-6 md:h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-[10px] md:text-xs">✓</span>
                                현장 실측 및 무료 견적
                            </li>
                        </ul>

                        <a href="<?php echo G5_BBS_URL . '/write.php?bo_table=consult'; ?>"
                            class="w-full py-3 md:py-5 bg-gray-900 text-white font-bold rounded-lg md:rounded-xl text-center text-sm md:text-lg hover:bg-orange-600 transition-colors shadow-lg flex items-center justify-center gap-1 md:gap-2 mt-auto">
                            1분 퀵 상담
                        </a>
                    </div>
                </div>

                <div
                    class="group relative bg-slate-800 rounded-2xl md:rounded-3xl p-0.5 md:p-1 shadow-2xl hover:-translate-y-2 transition-all duration-300 border border-slate-700">
                    <div
                        class="relative bg-slate-800 h-full rounded-[14px] md:rounded-[20px] p-4 md:p-10 flex flex-col">
                        <div
                            class="w-10 h-10 md:w-16 md:h-16 bg-slate-700 text-green-400 rounded-xl md:rounded-2xl flex items-center justify-center mb-3 md:mb-6 text-xl md:text-3xl">
                            💬
                        </div>

                        <h3 class="text-lg md:text-3xl font-bold text-white mb-2 md:mb-3">
                            실시간 채팅
                        </h3>
                        <p class="text-slate-400 mb-4 md:mb-8 leading-relaxed text-xs md:text-lg">
                            전화가 부담스러우신가요?<br class="hidden md:block">
                            카톡으로 편하게 물어보세요.
                        </p>

                        <ul class="space-y-2 md:space-y-4 mb-4 md:mb-10 flex-1 hidden md:block">
                            <li class="flex items-center gap-2 md:gap-3 text-slate-300 text-xs md:text-base">
                                <span
                                    class="w-4 h-4 md:w-6 md:h-6 rounded-full bg-slate-700 text-green-400 flex items-center justify-center text-[10px] md:text-xs">✓</span>
                                평일 09:00 - 18:00 실시간
                            </li>
                            <li class="flex items-center gap-2 md:gap-3 text-slate-300 text-xs md:text-base">
                                <span
                                    class="w-4 h-4 md:w-6 md:h-6 rounded-full bg-slate-700 text-green-400 flex items-center justify-center text-[10px] md:text-xs">✓</span>
                                현장 사진으로 간편 견적
                            </li>
                        </ul>

                        <div class="flex gap-2 md:gap-3 mt-auto">
                            <a href="http://pf.kakao.com/_IuIan" target="_blank"
                                class="flex-1 bg-[#FAE100] text-[#371D1E] font-bold py-3 md:py-5 rounded-lg md:rounded-xl text-center text-sm md:text-base hover:bg-opacity-90 transition-all flex items-center justify-center gap-1">
                                카카오톡
                            </a>
                            <a href="https://talk.naver.com/profile/wc2lsr" target="_blank"
                                class="flex-1 bg-[#00DE5A] text-white font-bold py-3 md:py-5 rounded-lg md:rounded-xl text-center text-sm md:text-base hover:bg-opacity-90 transition-all flex items-center justify-center gap-1">
                                네이버
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- ==============================================
         [4] 간판대학.ZIP - 프리미엄 포트폴리오 카드
         ============================================== -->
    <!-- ==============================================
         [4] 간판대학.ZIP - 프리미엄 포트폴리오 카드 (Latest Skin Linked)
         ============================================== -->
    <section class="py-20 bg-gradient-to-b from-white to-gray-50">
        <div class="max-w-7xl mx-auto px-4">
            <div class="text-center mb-16">
                <h2 class="text-4xl md:text-5xl font-black text-gray-900 mb-4">
                    <span class="text-orange-500">간판대학</span>.ZIP
                </h2>
                <p class="text-xl text-gray-600">
                    생생한 제작 사례로 확인하는 퀄리티
                </p>
            </div>

            <?php echo latest('portfolio_banner', 'ca_portfolio', 6, 40, 0); ?>

            <div class="flex justify-center mt-12">
                <a href="<?php echo G5_BBS_URL . '/board.php?bo_table=ca_portfolio'; ?>"
                    class="px-8 py-4 bg-gray-900 text-white rounded-full font-bold hover:bg-orange-500 transition-all shadow-lg hover:shadow-xl">
                    포트폴리오 전체보기 →
                </a>
            </div>
        </div>
    </section>

    <!-- ==============================================
         [New] Before & After Section
         ============================================== -->
    <section class="py-20 bg-white border-t border-gray-100">
        <div class="max-w-7xl mx-auto px-4">


            <!-- ==============================================
         [New] Before & After Section (Advanced)
         ============================================== -->
            <style>
                /* Before & After Custom CSS */
                .before-after-card {
                    background: white;
                    border-radius: 16px;
                    overflow: hidden;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                    height: auto;
                }

                .before-after-card:hover {
                    transform: translateY(-8px);
                    box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
                }

                .image-comparison-wrapper {
                    position: relative;
                    width: 100%;
                    height: 300px;
                    overflow: hidden;
                    cursor: col-resize;
                    background: #f0f0f0;
                }

                .comparison-image {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    object-fit: cover;
                }

                .before-image {
                    z-index: 1;
                }

                .after-image-container {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 50%;
                    height: 100%;
                    overflow: hidden;
                    z-index: 2;
                    border-right: 2px solid white;
                }

                .after-image {
                    position: absolute;
                    top: 0;
                    left: 0;
                    width: 200%;
                    height: 100%;
                    /* Important: 200% width to compensate container width */
                    object-fit: cover;
                }

                .slider-handle {
                    position: absolute;
                    top: 0;
                    left: 50%;
                    width: 6px;
                    height: 100%;
                    background: white;
                    z-index: 3;
                    transform: translateX(-50%);
                    cursor: col-resize;
                    box-shadow: 0 0 10px rgba(0, 0, 0, 0.3);
                }

                .slider-handle::before {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    width: 56px;
                    height: 56px;
                    background: white;
                    border-radius: 50%;
                    transform: translate(-50%, -50%);
                    box-shadow: 0 6px 20px rgba(0, 0, 0, 0.25);
                    border: 3px solid #f97316;
                    transition: all 0.3s ease;
                }

                .image-comparison-wrapper:hover .slider-handle::before {
                    background: #f97316;
                    transform: translate(-50%, -50%) scale(1.1);
                    box-shadow: 0 8px 24px rgba(249, 115, 22, 0.4);
                }

                .slider-handle::after {
                    content: '';
                    position: absolute;
                    top: 50%;
                    left: 50%;
                    transform: translate(-50%, -50%);
                    width: 24px;
                    height: 24px;
                    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="%23333" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8L22 12L18 16"/><path d="M6 8L2 12L6 16"/><line x1="2" y1="12" x2="22" y2="12"/></svg>');
                    background-size: contain;
                    background-repeat: no-repeat;
                    background-position: center;
                    z-index: 1;
                    transition: all 0.3s ease;
                }

                .image-comparison-wrapper:hover .slider-handle::after {
                    background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="white" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8L22 12L18 16"/><path d="M6 8L2 12L6 16"/><line x1="2" y1="12" x2="22" y2="12"/></svg>');
                }

                .comparison-label {
                    position: absolute;
                    top: 20px;
                    padding: 8px 16px;
                    color: white;
                    font-size: 14px;
                    font-weight: 700;
                    border-radius: 8px;
                    z-index: 4;
                    letter-spacing: 1px;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
                    transition: all 0.3s ease;
                }

                .image-comparison-wrapper:hover .comparison-label {
                    transform: scale(1.05);
                    box-shadow: 0 6px 16px rgba(0, 0, 0, 0.4);
                }

                .label-before {
                    left: 20px;
                    background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
                }

                .label-after {
                    right: 20px;
                    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
                }

                .slider-hint {
                    position: absolute;
                    bottom: 20px;
                    left: 50%;
                    transform: translateX(-50%);
                    padding: 8px 16px;
                    background: rgba(0, 0, 0, 0.7);
                    color: white;
                    font-size: 13px;
                    border-radius: 20px;
                    z-index: 4;
                    opacity: 0;
                    animation: fadeInOut 3s ease-in-out infinite;
                    pointer-events: none;
                }

                @keyframes fadeInOut {

                    0%,
                    100% {
                        opacity: 0;
                    }

                    10%,
                    90% {
                        opacity: 1;
                    }
                }

                .image-comparison-wrapper:hover .slider-hint {
                    animation: none;
                    opacity: 0;
                }

                @media (max-width: 768px) {
                    .image-comparison-wrapper {
                        height: 250px;
                    }

                    .slider-handle {
                        width: 5px;
                    }

                    .slider-handle::before {
                        width: 48px;
                        height: 48px;
                        border-width: 2px;
                    }

                    .slider-handle::after {
                        width: 20px;
                        height: 20px;
                    }

                    .comparison-label {
                        font-size: 12px;
                        padding: 6px 12px;
                    }

                    .slider-hint {
                        font-size: 11px;
                        padding: 6px 12px;
                    }
                }
            </style>

            <section class="pt-20 pb-40 bg-white border-t border-gray-100">
                <div class="max-w-7xl mx-auto px-4">
                    <div class="text-center mb-16">
                        <h2 class="text-4xl md:text-5xl font-black text-gray-900 mb-2">
                            Before & After
                        </h2>
                        <p class="text-base md:text-lg text-gray-500 font-normal">
                            이렇게 달라졌어요
                        </p>
                    </div>

                    <!-- Grid Layout (Fixed 3 Items) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-8 pb-12">
                        <?php
                        // Before & After 게시판에서 최신 3개만 가져오기
                        $ba_sql = "SELECT wr_id, wr_subject, wr_content, wr_1, wr_hit FROM {$g5['write_prefix']}beforeafter WHERE wr_is_comment = 0 ORDER BY wr_id DESC LIMIT 3";
                        $ba_result = sql_query($ba_sql);

                        for ($i = 0; $row = sql_fetch_array($ba_result); $i++) {
                            // 파일 정보 가져오기 (Before: 파일0 / After: 파일1)
                            // 전략: 이미지 #1(목록 썸네일용)을 'After(완성)'로, 이미지 #2를 'Before(전)'로 사용
                            $sql_file = "SELECT bf_no, bf_file FROM {$g5['board_file_table']} WHERE bo_table = 'beforeafter' AND wr_id = '{$row['wr_id']}' ORDER BY bf_no ASC";
                            $file_result = sql_query($sql_file);

                            $before_img = 'https://placehold.co/800x600/ddd/666?text=Before';
                            $after_img = 'https://placehold.co/800x600/4CAF50/fff?text=After';

                            while ($file_row = sql_fetch_array($file_result)) {
                                if ($file_row['bf_no'] == 0)
                                    $after_img = G5_DATA_URL . '/file/beforeafter/' . $file_row['bf_file'];
                                if ($file_row['bf_no'] == 1)
                                    $before_img = G5_DATA_URL . '/file/beforeafter/' . $file_row['bf_file'];
                            }

                            $location = $row['wr_1'] ? $row['wr_1'] : '위치 정보 확인';
                            $subject = $row['wr_subject'];
                            $content = cut_str(strip_tags($row['wr_content']), 80);
                            ?>
                            <div class="h-auto">
                                <div class="before-after-card h-full flex flex-col">
                                    <!-- Image Comparison Slider -->
                                    <!-- Image Comparison Slider -->
                                    <div class="image-comparison-wrapper bg-gray-100">
                                        <!-- Background Image (Right Side - After) -->
                                        <img src="<?php echo $after_img; ?>" alt="After"
                                            class="comparison-image before-image">

                                        <!-- Overlay Image (Left Side - Before) -->
                                        <div class="after-image-container">
                                            <img src="<?php echo $before_img; ?>" alt="Before"
                                                class="comparison-image after-image">
                                        </div>

                                        <!-- Drag Handle -->
                                        <div class="slider-handle"></div>

                                        <!-- Labels -->
                                        <span class="comparison-label label-before">Before</span>
                                        <span class="comparison-label label-after">After</span>

                                        <!-- Hint Text -->
                                        <div class="slider-hint">마우스를 좌우로 움직여보세요</div>

                                        <!-- Link Overlay (Clickable) -->
                                        <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=beforeafter&wr_id=<?php echo $row['wr_id']; ?>"
                                            class="absolute inset-0 z-10" target="_blank" title="자세히 보기"></a>
                                    </div>

                                    <!-- Text Content -->
                                    <div class="p-6 flex flex-col flex-1">
                                        <a
                                            href="<?php echo G5_BBS_URL ?>/board.php?bo_table=beforeafter&wr_id=<?php echo $row['wr_id']; ?>">
                                            <h3
                                                class="font-bold text-lg mb-2 text-gray-900 line-clamp-1 hover:text-orange-500 transition-colors">
                                                <?php echo $subject; ?>
                                            </h3>
                                        </a>
                                        <p class="text-sm text-gray-500 mb-4 line-clamp-2 flex-1">
                                            <?php echo $content; ?>
                                        </p>
                                        <div
                                            class="flex items-center justify-between text-xs text-gray-400 border-t border-gray-100 pt-4 mt-auto">
                                            <span class="flex items-center gap-1 font-medium text-gray-500">📍
                                                <?php echo preg_replace('/#[^ ]+/', '', $location); ?></span>
                                            <span>조회 <?php echo number_format($row['wr_hit']); ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php } ?>

                        <?php if ($i == 0) { // 게시물이 없을 경우 ?>
                            <div
                                class="col-span-1 md:col-span-2 lg:col-span-3 text-center py-12 text-gray-400 bg-gray-50 rounded-2xl border-2 border-dashed border-gray-200">
                                <p>등록된 게시물이 없습니다.</p>
                            </div>
                        <?php } ?>
                    </div>

                    <!-- View All Button -->
                    <div class="text-center mt-16">
                        <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=beforeafter"
                            class="inline-block px-8 py-4 bg-gray-900 text-white rounded-full font-bold hover:bg-orange-500 transition-all shadow-lg hover:shadow-xl">
                            Before & After 전체보기 →
                        </a>
                    </div>
                </div>
            </section>



            <!-- ==============================================
         [5] 간판대학 이용후기 (Swiper 슬라이더)
         ============================================== -->
            <section class="py-20 bg-gray-50">
                <div class="max-w-7xl mx-auto px-4">
                    <div class="text-center mb-16">
                        <h2 class="text-4xl md:text-5xl font-black text-gray-900 mb-4">
                            간판대학 <span class="text-orange-500">이용후기</span>
                        </h2>
                        <p class="text-xl text-gray-600">
                            고객님의 진짜 경험담
                        </p>
                    </div>

                    <!-- Swiper 슬라이더 -->
                    <div class="swiper review-swiper">
                        <div class="swiper-wrapper">
                            <?php
                            $review_sql = "SELECT w.wr_id, w.wr_subject, w.wr_content, w.wr_datetime, w.wr_name, w.wr_1, f.bf_file
                                   FROM {$g5['write_prefix']}review w
                                   LEFT JOIN {$g5['board_file_table']} f ON f.bo_table = 'review' AND f.wr_id = w.wr_id AND f.bf_no = 0
                                   WHERE w.wr_is_comment = 0
                                   ORDER BY w.wr_id DESC
                                   LIMIT 12";

                            $review_result = sql_query($review_sql);

                            if ($review_result && sql_num_rows($review_result) > 0) {
                                while ($review = sql_fetch_array($review_result)) {
                                    $review_subject = strip_tags($review['wr_subject']);
                                    $review_subject = mb_substr($review_subject, 0, 30);

                                    $review_content = strip_tags($review['wr_content']);
                                    $review_content = mb_substr($review_content, 0, 100);

                                    $review_img = '';
                                    if (!empty($review['bf_file'])) {
                                        $review_img = G5_DATA_URL . '/file/review/' . $review['bf_file'];
                                    } else {
                                        $review_img = 'https://placehold.co/400x300?text=Review';
                                    }

                                    $location = $review['wr_1'] ? $review['wr_1'] : '위치 미등록';
                                    ?>
                                    <div class="swiper-slide">
                                        <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=review&wr_id=<?php echo $review['wr_id']; ?>"
                                            class="block bg-white rounded-2xl overflow-hidden shadow-lg hover:shadow-2xl transition-all duration-300 h-full card-hover">
                                            <div class="relative h-64">
                                                <img src="<?php echo $review_img; ?>"
                                                    alt="<?php echo htmlspecialchars($review_subject); ?>"
                                                    class="w-full h-full object-cover">
                                                <div class="absolute top-4 left-4">
                                                    <span
                                                        class="px-3 py-1 bg-orange-500 text-white text-xs font-bold rounded-full">
                                                        ⭐ 5.0
                                                    </span>
                                                </div>
                                            </div>
                                            <div class="p-6">
                                                <h3 class="font-bold text-lg mb-2 text-gray-900">
                                                    <?php echo htmlspecialchars($review_subject); ?>
                                                </h3>
                                                <p class="text-sm text-gray-600 mb-3 line-clamp-2">
                                                    <?php echo htmlspecialchars($review_content); ?>
                                                </p>
                                                <div class="flex items-center justify-between text-xs text-gray-500">
                                                    <span>📍 <?php echo htmlspecialchars($location); ?></span>
                                                    <span><?php echo $review['wr_name']; ?></span>
                                                </div>
                                            </div>
                                        </a>
                                    </div>
                                    <?php
                                }
                            } else {
                                // 이용후기 없을 때 (Loop 모드를 위해 최소 6개 생성)
                                for ($i = 0; $i < 6; $i++) {
                                    ?>
                                    <div class="swiper-slide">
                                        <div class="bg-white rounded-2xl p-8 text-center shadow-lg">
                                            <div class="text-6xl mb-4">📝</div>
                                            <h3 class="font-bold text-xl mb-2">첫 번째 리뷰를 남겨주세요!</h3>
                                            <p class="text-gray-600">고객님의 소중한 경험을 공유해주세요</p>
                                        </div>
                                    </div>
                                    <?php
                                }
                            }
                            ?>
                        </div>
                        <!-- 네비게이션 버튼 -->
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                        <!-- 페이지네이션 -->
                        <div class="swiper-pagination"></div>
                    </div>
                </div>
            </section>

            <!-- ==============================================
         [6] SIGN NEWS.ZIP
         ============================================== -->
            <section class="py-20 bg-white">
                <div class="max-w-7xl mx-auto px-4">
                    <div class="text-center mb-16">
                        <h2 class="text-4xl md:text-5xl font-black text-gray-900 mb-4">
                            SIGN <span class="text-orange-500">NEWS</span>.ZIP
                        </h2>
                        <p class="text-xl text-gray-600">
                            간판 업계의 최신 소식과 트렌드
                        </p>
                    </div>

                    <div class="swiper news-swiper" style="width: 100%; overflow: hidden;">
                        <div class="swiper-wrapper py-4">
                            <?php
                            $sql = " SELECT 
                                w.wr_id,
                                w.wr_subject,
                                w.wr_content,
                                w.wr_name,
                                w.wr_datetime,
                                w.wr_hit,
                                w.wr_comment,
                                w.wr_1,
                                f.bf_file,
                                f.bf_content
                            FROM {$g5['write_prefix']}signnews w
                            LEFT JOIN {$g5['board_file_table']} f ON (f.bo_table = 'signnews' AND f.wr_id = w.wr_id AND f.bf_no = 0)
                            WHERE w.wr_is_comment = 0
                            ORDER BY w.wr_num DESC, w.wr_reply ASC
                            LIMIT 10";

                            $result = sql_query($sql);

                            while ($row = sql_fetch_array($result)) {
                                $subject = strip_tags($row['wr_subject']);
                                $subject = mb_substr($subject, 0, 50, 'UTF-8');
                                if (mb_strlen($subject, 'UTF-8') > 50) {
                                    $subject .= '...';
                                }

                                // 1. HTML 엔티티 제거 및 태그 제거
                                $content = html_entity_decode($row['wr_content']);
                                $content = strip_tags($content);

                                // 2. 공백 및 특수문자 정리
                                $content = str_replace(array('&nbsp;', '&amp;nbsp;'), ' ', $content);

                                // 3. 해시태그 제거 (강력한 정규식)
                                // #으로 시작하고 공백이 아닌 문자가 이어지는 패턴 제거
                                $content = preg_replace('/#[^\s#]+/', '', $content);

                                // 4. 연속된 공백 정리
                                $content = preg_replace('/\s+/', ' ', $content);
                                $content = trim($content);

                                // 5. 내용이 비어있으면 대체 텍스트 (옵션)
                                if (empty($content)) {
                                    $content = "내용이 없습니다."; // 혹은 그냥 빈칸
                                }

                                // 6. 길이 제한 (3줄을 채우기 위해 넉넉하게)
                                $content = mb_substr($content, 0, 100, 'UTF-8');
                                if (mb_strlen($content, 'UTF-8') > 100) {
                                    $content .= '...';
                                }

                                $image_src = (isset($row['bf_file']) && $row['bf_file'])
                                    ? G5_DATA_URL . '/file/signnews/' . $row['bf_file']
                                    : G5_THEME_IMG_URL . '/thumb_temp.jpg';
                                ?>
                                <div class="swiper-slide" style="height: auto;">
                                    <a href="<?php echo G5_BBS_URL . '/board.php?bo_table=signnews&wr_id=' . $row['wr_id']; ?>"
                                        class="block bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all overflow-hidden card-hover group"
                                        style="text-decoration: none; color: inherit; cursor: pointer;">
                                        <div class="card-image-container h-48">
                                            <img src="<?php echo $image_src; ?>"
                                                alt="<?php echo htmlspecialchars($subject); ?>"
                                                class="card-image w-full h-full object-cover">
                                            <div class="gradient-overlay"></div>
                                        </div>
                                        <div class="p-4">
                                            <h3
                                                class="font-bold text-gray-900 mb-1 text-[13px] line-clamp-2 leading-snug h-[2.8em]">
                                                <?php echo htmlspecialchars($subject); ?>
                                            </h3>
                                            <p
                                                class="text-gray-500 text-[11px] mb-2 line-clamp-3 leading-relaxed h-[4.5em]">
                                                <?php echo htmlspecialchars($content); ?>
                                            </p>
                                            <div class="text-[10px] text-gray-400">
                                                <?php echo date('Y.m.d', strtotime($row['wr_datetime'])); ?>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php } ?>
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>

                    <div class="flex justify-center mt-12">
                        <a href="<?php echo G5_BBS_URL . '/board.php?bo_table=signnews'; ?>"
                            class="px-8 py-4 bg-gray-200 hover:bg-gray-300 rounded-full font-bold text-gray-800 transition-all shadow-md">
                            뉴스 전체보기
                        </a>
                    </div>
                </div>
            </section>


            <!-- ==============================================
     [6] 떠다니는 견적 버튼
     ============================================== -->


            <!-- ==============================================
     [7] 견적 신청 모달
     ============================================== -->


            <!-- ==============================================
     [8] 30초 간판 추천 퀴즈 모달
     ============================================== -->
            <div id="quizModal" class="fixed inset-0 z-[9999] hidden">
                <div class="absolute inset-0 bg-black/60 backdrop-blur-sm transition-opacity opacity-0"
                    id="quizBackdrop" onclick="closeQuizModal()"></div>

                <div class="absolute inset-0 flex items-center justify-center p-4 pointer-events-none">
                    <div id="quizContent"
                        class="bg-white w-full max-w-2xl rounded-3xl shadow-2xl relative transform scale-0 opacity-0 transition-all duration-300 overflow-hidden pointer-events-auto">

                        <!-- 퀴즈 헤더 -->
                        <div class="bg-gradient-to-r from-orange-500 to-orange-400 px-8 py-6">
                            <div class="flex justify-between items-center">
                                <div>
                                    <h3 class="font-black text-2xl text-white mb-1">🎯 30초 간판 추천</h3>
                                    <p class="text-white text-opacity-90 text-sm">몇 가지 질문으로 최적의 간판을 찾아드려요</p>
                                </div>
                                <button onclick="closeQuizModal()"
                                    class="text-white hover:text-gray-200 transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                        stroke-width="2.5" stroke="currentColor" class="w-7 h-7">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- 진행 바 -->
                            <div class="progress-bar mt-4">
                                <div class="progress-fill" id="progressBar" style="width: 0%"></div>
                            </div>
                            <div class="text-white text-sm mt-2">
                                <span id="currentStep">1</span> / 5 단계
                            </div>
                        </div>

                        <!-- 퀴즈 콘텐츠 -->
                        <div class="p-8 min-h-[400px]">
                            <!-- 질문 1 -->
                            <div class="quiz-step" data-step="1">
                                <h4 class="text-2xl font-bold text-gray-900 mb-6">어떤 업종이신가요?</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <button onclick="selectQuizAnswer(1, '음식점')"
                                        class="quiz-option p-6 bg-gray-50 hover:bg-orange-50 hover:border-orange-500 border-2 border-gray-200 rounded-xl transition-all text-left">
                                        <div class="text-3xl mb-2">🍽️</div>
                                        <div class="font-bold text-gray-900">음식점</div>
                                        <div class="text-sm text-gray-600">카페, 레스토랑, 분식 등</div>
                                    </button>
                                    <button onclick="selectQuizAnswer(1, '소매점')"
                                        class="quiz-option p-6 bg-gray-50 hover:bg-orange-50 hover:border-orange-500 border-2 border-gray-200 rounded-xl transition-all text-left">
                                        <div class="text-3xl mb-2">🛍️</div>
                                        <div class="font-bold text-gray-900">소매점</div>
                                        <div class="text-sm text-gray-600">의류, 잡화, 편의점 등</div>
                                    </button>
                                    <button onclick="selectQuizAnswer(1, '서비스업')"
                                        class="quiz-option p-6 bg-gray-50 hover:bg-orange-50 hover:border-orange-500 border-2 border-gray-200 rounded-xl transition-all text-left">
                                        <div class="text-3xl mb-2">💇</div>
                                        <div class="font-bold text-gray-900">서비스업</div>
                                        <div class="text-sm text-gray-600">미용실, 네일샵, 학원 등</div>
                                    </button>
                                    <button onclick="selectQuizAnswer(1, '기타')"
                                        class="quiz-option p-6 bg-gray-50 hover:bg-orange-50 hover:border-orange-500 border-2 border-gray-200 rounded-xl transition-all text-left">
                                        <div class="text-3xl mb-2">🏢</div>
                                        <div class="font-bold text-gray-900">기타</div>
                                        <div class="text-sm text-gray-600">사무실, 병원, 기타 등</div>
                                    </button>
                                </div>
                            </div>

                            <!-- 질문 2 -->
                            <div class="quiz-step hidden" data-step="2">
                                <h4 class="text-2xl font-bold text-gray-900 mb-6">가게 위치는 어디인가요?</h4>
                                <div class="grid grid-cols-1 gap-4">
                                    <button onclick="selectQuizAnswer(2, '1층 로드샵')"
                                        class="quiz-option p-6 bg-gray-50 hover:bg-orange-50 hover:border-orange-500 border-2 border-gray-200 rounded-xl transition-all text-left flex items-center gap-4">
                                        <div class="text-4xl">🏪</div>
                                        <div>
                                            <div class="font-bold text-gray-900">1층 로드샵</div>
                                            <div class="text-sm text-gray-600">거리에서 바로 보이는 위치</div>
                                        </div>
                                    </button>
                                    <button onclick="selectQuizAnswer(2, '건물 상층')"
                                        class="quiz-option p-6 bg-gray-50 hover:bg-orange-50 hover:border-orange-500 border-2 border-gray-200 rounded-xl transition-all text-left flex items-center gap-4">
                                        <div class="text-4xl">🏢</div>
                                        <div>
                                            <div class="font-bold text-gray-900">건물 상층</div>
                                            <div class="text-sm text-gray-600">2층 이상 또는 건물 내부</div>
                                        </div>
                                    </button>
                                    <button onclick="selectQuizAnswer(2, '지하')"
                                        class="quiz-option p-6 bg-gray-50 hover:bg-orange-50 hover:border-orange-500 border-2 border-gray-200 rounded-xl transition-all text-left flex items-center gap-4">
                                        <div class="text-4xl">⬇️</div>
                                        <div>
                                            <div class="font-bold text-gray-900">지하</div>
                                            <div class="text-sm text-gray-600">지하 상가 또는 지하철역 근처</div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- 질문 3 -->
                            <div class="quiz-step hidden" data-step="3">
                                <h4 class="text-2xl font-bold text-gray-900 mb-6">간판 설치 예산은 어느 정도인가요?</h4>
                                <div class="grid grid-cols-1 gap-4">
                                    <button onclick="selectQuizAnswer(3, '100만원 이하')"
                                        class="quiz-option p-6 bg-gray-50 hover:bg-orange-50 hover:border-orange-500 border-2 border-gray-200 rounded-xl transition-all text-left">
                                        <div class="font-bold text-gray-900 mb-2">💰 100만원 이하</div>
                                        <div class="text-sm text-gray-600">기본형 채널간판, LED 간판</div>
                                    </button>
                                    <button onclick="selectQuizAnswer(3, '100~300만원')"
                                        class="quiz-option p-6 bg-gray-50 hover:bg-orange-50 hover:border-orange-500 border-2 border-gray-200 rounded-xl transition-all text-left">
                                        <div class="font-bold text-gray-900 mb-2">💰💰 100~300만원</div>
                                        <div class="text-sm text-gray-600">입체채널, 아크릴간판, LED 돌출간판</div>
                                    </button>
                                    <button onclick="selectQuizAnswer(3, '300만원 이상')"
                                        class="quiz-option p-6 bg-gray-50 hover:bg-orange-50 hover:border-orange-500 border-2 border-gray-200 rounded-xl transition-all text-left">
                                        <div class="font-bold text-gray-900 mb-2">💰💰💰 300만원 이상</div>
                                        <div class="text-sm text-gray-600">프리미엄 입체간판, 대형 LED 간판</div>
                                    </button>
                                </div>
                            </div>

                            <!-- 질문 4 -->
                            <div class="quiz-step hidden" data-step="4">
                                <h4 class="text-2xl font-bold text-gray-900 mb-6">선호하는 간판 스타일은?</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <button onclick="selectQuizAnswer(4, '모던')"
                                        class="quiz-option p-6 bg-gray-50 hover:bg-orange-50 hover:border-orange-500 border-2 border-gray-200 rounded-xl transition-all text-left">
                                        <div class="text-3xl mb-2">✨</div>
                                        <div class="font-bold text-gray-900">모던·세련</div>
                                        <div class="text-sm text-gray-600">심플하고 깔끔한 느낌</div>
                                    </button>
                                    <button onclick="selectQuizAnswer(4, '화려')"
                                        class="quiz-option p-6 bg-gray-50 hover:bg-orange-50 hover:border-orange-500 border-2 border-gray-200 rounded-xl transition-all text-left">
                                        <div class="text-3xl mb-2">🎨</div>
                                        <div class="font-bold text-gray-900">화려·눈에 띄는</div>
                                        <div class="text-sm text-gray-600">강렬한 색상과 디자인</div>
                                    </button>
                                    <button onclick="selectQuizAnswer(4, '빈티지')"
                                        class="quiz-option p-6 bg-gray-50 hover:bg-orange-50 hover:border-orange-500 border-2 border-gray-200 rounded-xl transition-all text-left">
                                        <div class="text-3xl mb-2">🕰️</div>
                                        <div class="font-bold text-gray-900">빈티지·레트로</div>
                                        <div class="text-sm text-gray-600">옛날 감성의 따뜻한 느낌</div>
                                    </button>
                                    <button onclick="selectQuizAnswer(4, '프리미엄')"
                                        class="quiz-option p-6 bg-gray-50 hover:bg-orange-50 hover:border-orange-500 border-2 border-gray-200 rounded-xl transition-all text-left">
                                        <div class="text-3xl mb-2">👑</div>
                                        <div class="font-bold text-gray-900">프리미엄·고급</div>
                                        <div class="text-sm text-gray-600">명품 브랜드 같은 느낌</div>
                                    </button>
                                </div>
                            </div>

                            <!-- 질문 5 -->
                            <div class="quiz-step hidden" data-step="5">
                                <h4 class="text-2xl font-bold text-gray-900 mb-6">야간 영업을 하시나요?</h4>
                                <div class="grid grid-cols-2 gap-4">
                                    <button onclick="selectQuizAnswer(5, '네')"
                                        class="quiz-option p-6 bg-gray-50 hover:bg-orange-50 hover:border-orange-500 border-2 border-gray-200 rounded-xl transition-all text-left">
                                        <div class="text-4xl mb-2">🌙</div>
                                        <div class="font-bold text-gray-900">네, 야간 영업해요</div>
                                        <div class="text-sm text-gray-600">LED 조명 간판 추천</div>
                                    </button>
                                    <button onclick="selectQuizAnswer(5, '아니오')"
                                        class="quiz-option p-6 bg-gray-50 hover:bg-orange-50 hover:border-orange-500 border-2 border-gray-200 rounded-xl transition-all text-left">
                                        <div class="text-4xl mb-2">☀️</div>
                                        <div class="font-bold text-gray-900">아니오, 주간만 영업</div>
                                        <div class="text-sm text-gray-600">일반 간판으로 충분</div>
                                    </button>
                                </div>
                            </div>

                            <!-- 결과 화면 -->
                            <div class="quiz-step hidden" data-step="result">
                                <div class="text-center">
                                    <div class="text-6xl mb-4">🎉</div>
                                    <h4 class="text-3xl font-black text-gray-900 mb-4">완료!</h4>
                                    <p class="text-gray-600 mb-8">
                                        고객님께 추천드리는 간판은<br />
                                        <span class="text-2xl font-bold text-orange-500" id="recommendedSign">입체 채널
                                            간판</span>입니다!
                                    </p>

                                    <div class="bg-orange-50 border-2 border-orange-200 rounded-2xl p-6 mb-8">
                                        <div class="text-left space-y-3">
                                            <div class="flex items-start gap-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor"
                                                    class="w-6 h-6 text-orange-500 flex-shrink-0">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <div>
                                                    <div class="font-bold text-gray-900">예상 견적</div>
                                                    <div class="text-sm text-gray-600" id="estimatedPrice">150~250만원
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="flex items-start gap-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor"
                                                    class="w-6 h-6 text-orange-500 flex-shrink-0">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                <div>
                                                    <div class="font-bold text-gray-900">제작 기간</div>
                                                    <div class="text-sm text-gray-600">약 7~10일 소요</div>
                                                </div>
                                            </div>
                                            <div class="flex items-start gap-3">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                                                    stroke-width="2" stroke="currentColor"
                                                    class="w-6 h-6 text-orange-500 flex-shrink-0">
                                                    <path stroke-linecap="round" stroke-linejoin="round"
                                                        d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                                </svg>
                                                <div>
                                                    <div class="font-bold text-gray-900">특징</div>
                                                    <div class="text-sm text-gray-600" id="signFeatures">입체감이 뛰어나고
                                                        야간에도 빛나는
                                                        간판
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="space-y-3">
                                        <button onclick="openConsultModalFromQuiz()"
                                            class="w-full btn-primary text-white py-4 rounded-xl font-bold text-lg">
                                            정확한 견적 받기 →
                                        </button>
                                        <button onclick="closeQuizModal()"
                                            class="w-full bg-gray-100 text-gray-700 py-4 rounded-xl font-bold text-lg hover:bg-gray-200 transition-all">
                                            닫기
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php include_once(G5_THEME_PATH . '/tail.php'); ?>

            <!-- Swiper 스타일 -->
            <style>
                .swiper-button-next,
                .swiper-button-prev {
                    width: 44px !important;
                    height: 44px !important;
                    background-color: white !important;
                    border-radius: 50% !important;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15) !important;
                }

                .swiper-button-next:after,
                .swiper-button-prev:after {
                    font-size: 18px !important;
                    font-weight: bold !important;
                    color: #f97316 !important;
                }

                .swiper-button-next:hover,
                .swiper-button-prev:hover {
                    background-color: #f97316 !important;
                }

                .swiper-button-next:hover:after,
                .swiper-button-prev:hover:after {
                    color: white !important;
                }

                /* 기본 그누보드 푸터 숨기기 */
                #ft,
                #tail {
                    display: none !important;
                }
            </style>

            <!-- 모든 자바스크립트 -->
            <script>
                // ============================================== 
                // 견적 모달 제어
                // ============================================== 


                // ============================================== 
                // 퀴즈 모달 제어
                // ============================================== 
                let quizAnswers = {};
                let currentQuizStep = 1;

                function openQuizModal() {
                    const modal = document.getElementById('quizModal');
                    const backdrop = document.getElementById('quizBackdrop');
                    const content = document.getElementById('quizContent');

                    // 초기화
                    quizAnswers = {};
                    currentQuizStep = 1;
                    updateProgressBar();

                    modal.classList.remove('hidden');
                    setTimeout(() => {
                        backdrop.classList.remove('opacity-0');
                        content.classList.remove('scale-0', 'opacity-0');
                        content.classList.add('scale-100', 'opacity-100');
                    }, 10);
                }

                function closeQuizModal() {
                    const modal = document.getElementById('quizModal');
                    const backdrop = document.getElementById('quizBackdrop');
                    const content = document.getElementById('quizContent');

                    backdrop.classList.add('opacity-0');
                    content.classList.remove('scale-100', 'opacity-100');
                    content.classList.add('scale-0', 'opacity-0');
                    setTimeout(() => {
                        modal.classList.add('hidden');
                    }, 300);
                }

                function selectQuizAnswer(step, answer) {
                    quizAnswers[step] = answer;

                    if (step < 5) {
                        currentQuizStep = step + 1;
                        showQuizStep(currentQuizStep);
                        updateProgressBar();
                    } else {
                        // 마지막 질문 - 결과 표시
                        showQuizResult();
                    }
                }

                function showQuizStep(step) {
                    document.querySelectorAll('.quiz-step').forEach(el => {
                        el.classList.add('hidden');
                    });
                    document.querySelector(`.quiz-step[data-step="${step}"]`).classList.remove('hidden');
                }

                function updateProgressBar() {
                    const progress = (currentQuizStep / 5) * 100;
                    document.getElementById('progressBar').style.width = progress + '%';
                    document.getElementById('currentStep').textContent = currentQuizStep;
                }

                function showQuizResult() {
                    // 간단한 추천 로직
                    let recommendedSign = '입체 채널 간판';
                    let estimatedPrice = '150~250만원';
                    let signFeatures = '입체감이 뛰어나고 야간에도 빛나는 간판';

                    // 예산에 따른 추천
                    if (quizAnswers[3] === '100만원 이하') {
                        recommendedSign = 'LED 간판';
                        estimatedPrice = '50~100만원';
                        signFeatures = '저렴하면서도 밝고 눈에 잘 띄는 경제적인 간판';
                    } else if (quizAnswers[3] === '300만원 이상') {
                        recommendedSign = '프리미엄 입체 간판';
                        estimatedPrice = '300~500만원';
                        signFeatures = '고급스러운 디자인과 뛰어난 내구성을 자랑하는 프리미엄 간판';
                    }

                    // 스타일에 따른 추가 설명
                    if (quizAnswers[4] === '빈티지') {
                        recommendedSign = '네온사인 간판';
                        signFeatures = '레트로 감성의 따뜻한 네온 불빛이 매력적인 간판';
                    }

                    // 결과 표시
                    document.getElementById('recommendedSign').textContent = recommendedSign;
                    document.getElementById('estimatedPrice').textContent = estimatedPrice;
                    document.getElementById('signFeatures').textContent = signFeatures;

                    showQuizStep('result');
                    updateProgressBar();
                    document.getElementById('progressBar').style.width = '100%';
                    document.getElementById('currentStep').textContent = '완료';
                }

                function openConsultModalFromQuiz() {
                    closeQuizModal();
                    setTimeout(() => {
                        openConsultModal();
                    }, 400);
                }

                // ============================================== 
                // Swiper 초기화
                // ============================================== 
                window.addEventListener('load', function () {
                    if (typeof Swiper === 'undefined') return;

                    try {
                        // 이용후기 Swiper
                        const reviewSlidesCount = document.querySelectorAll('.review-swiper .swiper-slide').length;
                        new Swiper('.review-swiper', {
                            slidesPerView: 1.2,
                            centeredSlides: true,
                            spaceBetween: 20,
                            loop: reviewSlidesCount > 3, // 슬라이드가 충분할 때만 루프
                            autoplay: { delay: 5000, disableOnInteraction: false },
                            navigation: { nextEl: '.review-swiper .swiper-button-next', prevEl: '.review-swiper .swiper-button-prev' },
                            pagination: { el: '.review-swiper .swiper-pagination', clickable: true },
                            breakpoints: {
                                640: { slidesPerView: 2, spaceBetween: 20, centeredSlides: false },
                                768: { slidesPerView: 2, spaceBetween: 30, centeredSlides: false },
                                1024: { slidesPerView: 3, spaceBetween: 40, centeredSlides: false }
                            }
                        });

                        // Before & After Swiper (New) - REMOVED (Grid Layout)
                        // new Swiper('.before-after-swiper', { ... }); 

                        // 뉴스 Swiper
                        const newsSlidesCount = document.querySelectorAll('.news-swiper .swiper-slide').length;
                        new Swiper('.news-swiper', {
                            slidesPerView: 1,
                            spaceBetween: 20,
                            loop: newsSlidesCount > 3, // 슬라이드가 충분할 때만 루프
                            autoplay: { delay: 4000, disableOnInteraction: false },
                            navigation: { nextEl: '.swiper-button-next', prevEl: '.swiper-button-prev' },
                            breakpoints: {
                                768: { slidesPerView: 2, spaceBetween: 30 },
                                1024: { slidesPerView: 3, spaceBetween: 40 }
                            }

                        });

                        // 포트폴리오 Swiper (New)
                        new Swiper('.portfolio-swiper', {
                            slidesPerView: 1.2,
                            spaceBetween: 20,
                            loop: false,  // true → false로 변경
                            autoplay: { delay: 3000, disableOnInteraction: false },
                            pagination: { el: '.portfolio-swiper .swiper-pagination', clickable: true },
                            breakpoints: {
                                640: { slidesPerView: 2, spaceBetween: 20 },
                                1024: { slidesPerView: 4, spaceBetween: 30 }
                            }
                        });
                    } catch (error) {
                        console.error('Swiper Error:', error);
                    }

                    // ===== Before/After 이미지 슬라이더 기능 (호버 방식) =====
                    function initImageComparison() {
                        const comparisonWrappers = document.querySelectorAll('.image-comparison-wrapper');

                        comparisonWrappers.forEach(wrapper => {
                            const afterContainer = wrapper.querySelector('.after-image-container');
                            const sliderHandle = wrapper.querySelector('.slider-handle');
                            const labelBefore = wrapper.querySelector('.label-before');
                            const labelAfter = wrapper.querySelector('.label-after');
                            const images = wrapper.querySelectorAll('img');

                            let isActive = false; // 마우스가 영역 안에 있는지
                            let isTouching = false; // 터치 중인지 (모바일)

                            // 🔒 이미지 드래그 완전 차단
                            images.forEach(img => {
                                img.setAttribute('draggable', 'false');
                                img.style.userSelect = 'none';
                                img.style.webkitUserSelect = 'none';
                                img.style.webkitUserDrag = 'none';
                                img.style.pointerEvents = 'none';
                                img.ondragstart = function () { return false; };
                            });

                            // 공통 업데이트 함수
                            function updateSlider(percentage) {
                                // After 이미지와 핸들 위치 업데이트
                                afterContainer.style.width = percentage + '%';
                                sliderHandle.style.left = percentage + '%';

                                // [NEW] 라벨 숨김 로직
                                if(labelBefore) {
                                    labelBefore.style.opacity = (percentage < 15) ? '0' : '1';
                                }
                                if(labelAfter) {
                                    labelAfter.style.opacity = (percentage > 85) ? '0' : '1';
                                }
                            }

                            // 💡 호버 방식: 마우스만 움직여도 슬라이더 이동
                            function onMouseMove(e) {
                                if (!isActive && !isTouching) return;

                                const rect = wrapper.getBoundingClientRect();
                                const x = e.clientX - rect.left;

                                // 범위 제한 (0% ~ 100%)
                                let percentage = (x / rect.width) * 100;
                                percentage = Math.max(0, Math.min(100, percentage));

                                updateSlider(percentage);
                            }

                            // 마우스가 영역에 들어옴
                            wrapper.addEventListener('mouseenter', function () {
                                isActive = true;
                            });

                            // 마우스가 영역에서 나감
                            wrapper.addEventListener('mouseleave', function () {
                                isActive = false;
                            });

                            // 마우스 움직임 감지 (호버 방식)
                            wrapper.addEventListener('mousemove', onMouseMove);

                            // ===== 모바일: 터치 방식 유지 =====
                            let touchStartX = 0;
                            let hasMoved = false;

                            function onTouchStart(e) {
                                isTouching = true;
                                hasMoved = false;
                                touchStartX = e.touches[0].clientX;
                                e.preventDefault();
                            }

                            function onTouchMove(e) {
                                if (!isTouching) return;

                                e.preventDefault();

                                const rect = wrapper.getBoundingClientRect();
                                const x = e.touches[0].clientX - rect.left;

                                // 5px 이상 움직였는지 확인
                                if (Math.abs(e.touches[0].clientX - touchStartX) > 5) {
                                    hasMoved = true;
                                }

                                let percentage = (x / rect.width) * 100;
                                percentage = Math.max(0, Math.min(100, percentage));

                                updateSlider(percentage);
                            }

                            function onTouchEnd(e) {
                                isTouching = false;

                                // 드래그하지 않고 터치만 했다면 링크 클릭 허용
                                if (hasMoved) {
                                    e.preventDefault();
                                    e.stopPropagation();
                                }

                                setTimeout(() => {
                                    hasMoved = false;
                                }, 200);
                            }

                            // 터치 이벤트 (모바일)
                            wrapper.addEventListener('touchstart', onTouchStart, { passive: false });
                            wrapper.addEventListener('touchmove', onTouchMove, { passive: false });
                            wrapper.addEventListener('touchend', onTouchEnd, { passive: false });

                            // 🔒 컨텍스트 메뉴(우클릭) 방지
                            wrapper.addEventListener('contextmenu', function (e) {
                                e.preventDefault();
                            });

                            // 🔒 드래그 시작 차단
                            wrapper.addEventListener('dragstart', function (e) {
                                e.preventDefault();
                                return false;
                            });
                        });
                    }

                    // 실행
                    initImageComparison();

                    // 동적 콘텐츠 로딩 후 재초기화
                    setTimeout(initImageComparison, 1000);
                });
            </script>