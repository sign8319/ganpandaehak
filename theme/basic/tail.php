<?php
if (!defined('_GNUBOARD_'))
  exit; // 개별 페이지 접근 불가

if (G5_IS_MOBILE) {
  include_once(G5_THEME_MOBILE_PATH . '/tail.php');
  return;
}

if (G5_COMMUNITY_USE === false) {
  include_once(G5_THEME_SHOP_PATH . '/shop.tail.php');
  return;
}
?>

</div>
</div>
<footer class="bg-[#1a1c23] text-gray-300 pt-20 pb-10 border-t border-gray-800 font-sans relative z-10">
  <div class="max-w-7xl mx-auto px-6">
    <?php
    $admin_pages = ['admin_quote.php', 'admin_customer.php', 'admin_quote_step1.php', 'admin_assets.php'];
    if (in_array(basename($_SERVER['PHP_SELF']), $admin_pages)) {
      // 관리자 페이지에서는 하단 내용을 비웁니다.
    } else { ?>
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-12 gap-12 lg:gap-8 mb-16">

        <div class="lg:col-span-4 text-center md:text-left">
          <h2 class="text-3xl font-black text-white mb-6 tracking-tight">
            간판대학<span class="text-orange-500">.</span>
          </h2>
          <p class="text-gray-400 mb-8 leading-relaxed">
            생각을 현실로, 간판으로 완성합니다.<br>
            최고의 디자인과 시공 퀄리티를 약속드립니다.
          </p>
          <div class="flex gap-4 justify-center md:justify-start">
            <a href="http://pf.kakao.com/_IuIan" target="_blank"
              class="w-12 h-12 bg-[#FAE100] rounded-full flex items-center justify-center text-[#371D1E] hover:scale-110 transition-transform shadow-lg">
              <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                <path
                  d="M12 3c-4.97 0-9 3.185-9 7.115 0 2.557 1.707 4.8 4.27 6.054-.188.702-.682 2.545-.78 2.94-.122.49.178.483.376.351.27-.18 4.27-2.878 4.377-2.953.582.083 1.179.127 1.787.127 4.97 0 9-3.185 9-7.115S16.97 3 12 3z" />
              </svg>
            </a>
            <a href="https://talk.naver.com/profile/wc2lsr" target="_blank"
              class="w-12 h-12 bg-[#00DE5A] rounded-full flex items-center justify-center text-white hover:scale-110 transition-transform shadow-lg">
              <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path
                  d="M6 3C4.343 3 3 4.343 3 6v9c0 1.657 1.343 3 3 3h9.5l4.5 4v-4h.5c1.657 0 3-1.343 3-3V6c0-1.657-1.343-3-3-3H6zm4 8a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3zm6 0a1.5 1.5 0 1 1 0-3 1.5 1.5 0 0 1 0 3z" />
              </svg>
            </a>
          </div>
        </div>

        <div class="lg:col-span-2 hidden md:block">
          <h4 class="text-white font-bold text-lg mb-6">바로가기</h4>
          <ul class="text-gray-400 space-y-3">
            <li><a href="<?php echo G5_BBS_URL ?>/content.php?co_id=company"
                class="hover:text-orange-500 transition-colors">회사소개</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=ca_portfolio"
                class="hover:text-orange-500 transition-colors">포트폴리오</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=signnews"
                class="hover:text-orange-500 transition-colors">간판 뉴스</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=review"
                class="hover:text-orange-500 transition-colors">이용 후기</a></li>
            <li><a href="<?php echo G5_BBS_URL ?>/write.php?bo_table=consult"
                class="hover:text-orange-500 transition-colors">견적 신청</a></li>
          </ul>
        </div>

        <div class="lg:col-span-3 text-center md:text-left">
          <h4 class="text-white font-bold text-lg mb-6">고객센터</h4>
          <ul class="space-y-6">
            <li class="flex flex-col md:flex-row items-center md:items-start gap-3 justify-center md:justify-start">
              <span class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                </svg>
              </span>
              <div>
                <a href="tel:16008319"
                  class="font-bold text-white text-xl hover:text-orange-500 transition-colors">1600-8319</a>
                <div class="text-sm text-gray-500 mt-1">평일 09:00 - 18:00 (주말/공휴일 휴무)</div>
              </div>
            </li>
            <li class="flex flex-col md:flex-row items-center gap-3 text-sm justify-center md:justify-start">
              <span class="w-10 h-10 rounded-full bg-white/10 flex items-center justify-center text-white">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
              </span>
              <span>sign8319@naver.com</span>
            </li>
          </ul>
        </div>

        <div class="lg:col-span-3">
          <h4 class="text-white font-bold text-lg mb-6 hidden md:block">제작 품목</h4>
          <div class="flex flex-wrap gap-2 hidden md:flex">
            <span class="px-3 py-1 bg-gray-800 rounded-full text-xs text-gray-300 border border-gray-700">채널간판</span>
            <span class="px-3 py-1 bg-gray-800 rounded-full text-xs text-gray-300 border border-gray-700">LED간판</span>
            <span class="px-3 py-1 bg-gray-800 rounded-full text-xs text-gray-300 border border-gray-700">돌출간판</span>
            <span class="px-3 py-1 bg-gray-800 rounded-full text-xs text-gray-300 border border-gray-700">네온사인</span>
            <span class="px-3 py-1 bg-gray-800 rounded-full text-xs text-gray-300 border border-gray-700">스카시</span>
            <span class="px-3 py-1 bg-gray-800 rounded-full text-xs text-gray-300 border border-gray-700">입간판</span>
            <span class="px-3 py-1 bg-gray-800 rounded-full text-xs text-gray-300 border border-gray-700">현수막</span>
          </div>

          <a href="<?php echo G5_BBS_URL ?>/board.php?bo_table=payment"
            class="block w-full mt-6 py-4 bg-indigo-600 hover:bg-indigo-500 text-white text-center rounded-xl transition-all hover:-translate-y-1 shadow-lg group">
            <span class="text-2xl mr-2 group-hover:scale-110 inline-block transition-transform">💳</span>
            <span class="font-bold text-lg">고객 전용 결제센터 바로가기</span>
          </a>
        </div>
      </div>

      <div class="border-t border-gray-800 pt-8 mt-8">
        <div class="flex flex-col md:flex-row justify-between items-center gap-6">
          <div class="text-sm text-gray-500 space-y-2 text-center md:text-left">
            <div class="flex flex-wrap justify-center md:justify-start gap-x-4 gap-y-1">
              <span><b>대표자</b> 장대신</span>
              <span class="hidden md:inline">|</span>
              <span><b>사업자등록번호</b> 210-03-60358</span>
              <span class="hidden md:inline">|</span>
              <span><b>주소</b> 서울특별시 성북구 장월로23길 29-2 (장위동)</span>
            </div>
            <p class="mt-2">COPYRIGHT Ⓒ 2025 간판대학. ALL RIGHTS RESERVED.</p>
          </div>

          <div class="flex gap-4 text-xs text-gray-500 font-medium">
            <a href="<?php echo G5_BBS_URL ?>/content.php?co_id=provision"
              class="hover:text-white transition-colors">이용약관</a>
            <a href="<?php echo G5_BBS_URL ?>/content.php?co_id=privacy"
              class="hover:text-white transition-colors">개인정보처리방침</a>
            <a href="<?php echo G5_BBS_URL ?>/content.php?co_id=emailreject"
              class="hover:text-white transition-colors">이메일무단수집거부</a>
          </div>
        </div>
      </div>
    <?php } ?>
  </div>
</footer>

<!-- ==============================================
     [6] 떠다니는 견적 버튼 (Global)
     ============================================== -->
<?php
// 관리자 페이지 배열 정의 (위에서 정의했지만 안전하게 다시 정의하거나 사용)
$admin_skip_pages = ['admin_quote.php', 'admin_customer.php', 'admin_quote_step1.php', 'admin_assets.php'];
if ((!isset($bo_table) || $bo_table != 'consult') && !in_array(basename($_SERVER['PHP_SELF']), $admin_skip_pages)) { // 간판의뢰페이지(consult) 및 관리자페이지에서는 숨김 ?>
  <button onclick="openConsultModal()"
    class="fixed bottom-6 right-6 z-[9000] flex items-center gap-3 bg-gradient-to-r from-orange-500 to-orange-600 text-white px-4 py-3 md:px-6 md:py-4 rounded-full shadow-2xl hover:shadow-3xl hover:scale-110 transition-all duration-300 cursor-pointer animate-pulse-glow">

    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"
      class="w-5 h-5 md:w-7 md:h-7">
      <path stroke-linecap="round" stroke-linejoin="round"
        d="M7.5 8.25h9m-9 3H12m-9.75 1.51c0 1.6 1.123 2.994 2.707 3.227 1.129.166 2.27.293 3.423.379.35.026.67.21.865.501L12 21l2.755-4.133a1.14 1.14 0 01.865-.501 48.172 48.172 0 003.423-.379c1.584-.233 2.707-1.626 2.707-3.228V6.741c0-1.602-1.123-2.995-2.707-3.228A48.394 48.394 0 0012 3c-2.392 0-4.744.175-7.043.513C3.373 3.746 2.25 5.14 2.25 6.741v6.018z" />
    </svg>

    <div class="flex flex-col items-start">
      <span class="text-[8px] md:text-[10px] opacity-90 leading-none mb-1">고민하지 마세요!</span>
      <span class="text-sm md:text-lg font-bold leading-none">무료 견적 신청</span>
    </div>
  </button>
<?php } ?>

<!-- ==============================================
     [7] 견적 신청 모달 (Global)
     ============================================== -->
<div id="consultModal" class="fixed inset-0 z-[9999] hidden">
  <div class="absolute inset-0 bg-black/50 backdrop-blur-sm transition-opacity opacity-0" id="modalBackdrop"
    onclick="closeConsultModal()"></div>

  <div
    class="absolute inset-0 flex items-center justify-center md:items-end md:justify-end p-4 sm:p-6 pointer-events-none">
    <div id="modalContent"
      class="bg-white w-full max-w-[480px] h-[90vh] md:h-[80vh] rounded-2xl shadow-2xl relative transform scale-0 opacity-0 origin-bottom-right transition-all duration-300 flex flex-col overflow-hidden pointer-events-auto md:mb-20 md:mr-2 border border-gray-200">

      <div
        class="flex justify-between items-center px-4 py-3 border-b border-gray-200 bg-gradient-to-r from-orange-500 to-orange-400">
        <h3 class="font-bold text-lg text-white">📋 무료 견적 신청</h3>
        <button onclick="closeConsultModal()" class="text-white hover:text-gray-200 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5"
            stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <div class="flex-1 w-full bg-white relative">
        <div class="absolute inset-0 flex items-center justify-center bg-white z-0" id="iframeLoader">
          <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-orange-500"></div>
        </div>
        <iframe id="consultIframe" class="w-full h-full relative z-10 border-none" title="견적신청"
          allow="forms-submit"></iframe>
      </div>
    </div>
  </div>
</div>



<?php
if (G5_DEVICE_BUTTON_DISPLAY && !G5_IS_MOBILE) { ?>
<?php
}

if ($config['cf_analytics']) {
  echo $config['cf_analytics'];
}
?>

<script>
  $(function () {
    // 폰트 리사이즈 쿠키있으면 실행
    font_resize("container", get_cookie("ck_font_resize_rmv_class"), get_cookie("ck_font_resize_add_class"));
  });

  // ============================================== 
  // 견적 모달 제어 (Global Script)
  // ============================================== 
  function openConsultModal() {
    const modal = document.getElementById('consultModal');
    const backdrop = document.getElementById('modalBackdrop');
    const content = document.getElementById('modalContent');
    const iframe = document.getElementById('consultIframe');
    const loader = document.getElementById('iframeLoader');

    // 동적 src 할당 (페이지 로드시 오류 방지)
    if (!iframe.getAttribute('src')) {
      iframe.setAttribute('src', "<?php echo G5_BBS_URL ?>/write.php?bo_table=consult&iframe_mode=1");
    }

    modal.classList.remove('hidden');
    setTimeout(() => {
      backdrop.classList.remove('opacity-0');
      content.classList.remove('scale-0', 'opacity-0');
      content.classList.add('scale-100', 'opacity-100');
    }, 10);

    // iframe 로딩 완료 시 로더 숨기기
    iframe.onload = function () {
      setTimeout(() => {
        if (loader) loader.style.display = 'none';
      }, 500);

      try {
        const innerDoc = iframe.contentDocument || iframe.contentWindow.document;
        const style = innerDoc.createElement('style');
        style.textContent = `
            header, footer, #hd, #ft, .gnb_wrap, #hd_pop, .hd_pop, #tnb, .sound_only, #side, .hd_login, .bo_w_tit { display: none !important; }
            body { padding: 0 !important; margin: 0 !important; background: #fff !important; overflow-x: hidden; }
            #wrapper, #container { width: 100% !important; min-width: 100% !important; padding: 0 !important; margin: 0 !important; }
            #bo_w { width: 100% !important; margin: 0 auto !important; padding: 15px !important; border: none !important; box-sizing: border-box !important; }
            .tbl_frm01, .frm_input { width: 100% !important; box-sizing: border-box !important; }
            ::-webkit-scrollbar { width: 6px; }
            ::-webkit-scrollbar-thumb { background: #ccc; border-radius: 10px; }
        `;
        innerDoc.head.appendChild(style);
      } catch (e) {
        console.log("iframe 스타일 적용 제한 (Same-Origin Policy)");
      }
    };
  }

  function closeConsultModal() {
    const modal = document.getElementById('consultModal');
    const backdrop = document.getElementById('modalBackdrop');
    const content = document.getElementById('modalContent');

    backdrop.classList.add('opacity-0');
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-0', 'opacity-0');
    setTimeout(() => {
      modal.classList.add('hidden');
    }, 300);
  }
</script>

<?php
include_once(G5_THEME_PATH . "/login.modal.php"); // 로그인 모달 포함
$admin_exclude_pages = ['admin_quote.php', 'admin_customer.php', 'admin_quote_step1.php', 'admin_assets.php', 'test_admin_quote.php', 'admin_quote_calc.php', 'admin_quote_calc_v2.php', 'admin_quote_manager.php'];
if (!in_array(basename($_SERVER['PHP_SELF']), $admin_exclude_pages)) {
  include_once(G5_THEME_PATH . "/skin/selection_widget.php"); // [NEW] 플로팅 선택 위젯
}
include_once(G5_THEME_PATH . "/tail.sub.php");