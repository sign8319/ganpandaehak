<?php
// [추가함] 팝업창(iframe)으로 열렸을 때 메뉴바, 푸터 숨기기
if(isset($_GET['iframe_mode']) && $_GET['iframe_mode'] == '1') {
    echo '<style>
        header, footer, #hd, #ft, .gnb_wrap, #hd_pop, .hd_pop, #tnb, .sound_only, #side, .hd_login, .ft { display: none !important; } 
        #wrapper, #container { padding:0 !important; margin:0 !important; width:100% !important; min-width:100% !important; }
        body { background: #fff !important; padding: 0 !important; overflow-x: hidden; }
        #bo_w { margin: 0 auto !important; width: 100% !important; border:0 !important; }
    </style>';
}
?>

<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 4단계: 진행중인 견적 건수 조회
// 4단계: 진행중인 견적 건수 조회 (관리자: 미처리 견적, 일반: 내 견적)
$quote_count = 0;
if ($is_member) {
    // g5_quote 테이블 존재 여부 확인 (에러 방지)
    $table_check = sql_fetch("SHOW TABLES LIKE 'g5_quote'");
    if ($table_check) {
        if ($member['mb_level'] >= 5) {
            // [ADMIN] 신규 신청완료 건수
            $sql = "SELECT COUNT(*) as cnt FROM g5_quote WHERE qa_status = '신청완료'";
        } else {
            // [MEMBER] 진행중인 내 견적
            $sql = "SELECT COUNT(*) as cnt FROM g5_quote 
                    WHERE mb_id = '{$member['mb_id']}' 
                    AND qa_status != '취소' 
                    AND qa_status != 'draft'";
        }
        $row = sql_fetch($sql);
        $quote_count = $row['cnt'];
    }
}

// [UX Config] 메뉴 설정
$quote_menu_label = ($is_member && $member['mb_level'] >= 5) ? '견적의뢰' : '진행중인 견적';
// 관리자: 목록 페이지로 이동 (신청완료 필터링 권장하지만 일단 전체 목록으로)
// 일반: 내 견적 내역
$quote_menu_url = ($is_member && $member['mb_level'] >= 5) 
    ? G5_THEME_URL . '/admin_quote.php' 
    : G5_URL . '/mypage/quote_history.php';

?>


<?php
if (G5_IS_MOBILE) {
    include_once(G5_THEME_MOBILE_PATH.'/head.php');
    return;
}

if(G5_COMMUNITY_USE === false) {
    define('G5_IS_COMMUNITY_PAGE', true);
    include_once(G5_THEME_SHOP_PATH.'/shop.head.php');
    return;
}
include_once(G5_THEME_PATH.'/head.sub.php');
include_once(G5_LIB_PATH.'/latest.lib.php');
include_once(G5_LIB_PATH.'/outlogin.lib.php');
include_once(G5_LIB_PATH.'/poll.lib.php');
include_once(G5_LIB_PATH.'/visit.lib.php');
include_once(G5_LIB_PATH.'/connect.lib.php');
include_once(G5_LIB_PATH.'/popular.lib.php');
// [FIX] 관리자 페이지(admin_*)에서는 상담 모달/공통폼 주입 방지
if (strpos(basename($_SERVER['PHP_SELF']), 'admin_') !== 0) {
    include_once(G5_THEME_PATH.'/modal_quick_consult.php');
}
?>

<?php
$main_menu = [
  [
    'label' => '간판의뢰',
    'href' => G5_BBS_URL . '/write.php?bo_table=consult',
  ],
  [
    'label' => '포트폴리오',
    'href' => '#',
    'children' => [
      ['label' => '업종별 포트폴리오', 'href' => G5_BBS_URL . '/board.php?bo_table=ca_portfolio'],
      ['label' => '종류별 포트폴리오', 'href' => G5_BBS_URL . '/board.php?bo_table=type_portfolio'],
      ['label' => 'Before&After', 'href' => G5_BBS_URL . '/board.php?bo_table=beforeafter'],
    ]
  ],
  [
    'label' => '간판가이드',
    'href' => '#',
    'children' => [
      ['label' => '간판 종류별 설명', 'href' => G5_BBS_URL . '/board.php?bo_table=signexp'],
      ['label' => '간판 진행 가이드', 'href' => G5_BBS_URL . '/board.php?bo_table=progressguide'],
      ['label' => 'A/S 및 유지보수', 'href' => G5_BBS_URL . '/board.php?bo_table=maintenance'],
      ['label' => '질문 FAQ', 'href' => G5_BBS_URL . '/board.php?bo_table=faq'],
    ]
  ],
  [
    'label' => 'SIGN.ZIP',
    'href' => '#',
    'children' => [
      ['label' => 'SIGN NEWS', 'href' => G5_BBS_URL . '/board.php?bo_table=signnews'],
      ['label' => '고객후기', 'href' => G5_BBS_URL . '/board.php?bo_table=review'],      
    ]
  ],
  [
    'label' => '스토어',
    'href' => G5_BBS_URL . '/board.php?bo_table=store',    
  ],
  [
    'label' => '블로그',
    'href' => 'https://blog.naver.com/sign8319',
    'target' => '_blank'   
  ],
];
?>

<?php
    if(defined('_INDEX_')) { // index에서만 실행
        include G5_BBS_PATH.'/newwin.inc.php'; // 팝업레이어
    }
?>


<div >
    <h1 id="hd_h1"><?php echo $g5['title'] ?></h1>
    <div id="skip_to_container"><a href="#container">본문 바로가기</a></div>

    
  <header class="w-full bg-white/95 backdrop-blur-md shadow-lg sticky top-0 z-50 transition-all duration-300">

    <nav class="bg-gradient-to-r from-orange-500 via-orange-400 to-orange-500 w-full mx-auto h-12 sm:h-14 md:h-16 flex items-center justify-center px-4 relative overflow-hidden">
      <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent animate-shimmer"></div>
      
      <img
        src="<?php echo G5_THEME_IMG_URL ?>/thumb.png"
        alt="상담 아이콘"
        class="w-6 h-6 sm:w-7 sm:h-7 md:w-8 md:h-8 mr-2 sm:mr-3 hidden sm:block relative z-10 animate-bounce"
      />
      <span class="text-white text-sm sm:text-base md:text-lg font-bold mr-2 sm:mr-4 relative z-10">
        🔥 고민 하지 마세요!
      </span>
      <div class="relative z-10">
        <a
          href="<?php echo G5_BBS_URL . '/write.php?bo_table=consult'; ?>"
          class="flex items-center bg-white rounded-full shadow-lg px-5 py-2 text-sm font-bold text-orange-600 hover:bg-orange-50 hover:scale-105 transition-all duration-300 ml-2"
        >
          ⚡ 1분 퀵 상담신청
        </a>
      </div>
    </nav>
    
    <style>
      @keyframes shimmer {
        0% { transform: translateX(-100%); }
        100% { transform: translateX(100%); }
      }
      .animate-shimmer {
        animation: shimmer 3s infinite;
      }
      
      /* 트렌디한 메뉴 호버 효과 */
      .menu-item {
        position: relative;
        transition: all 0.3s ease;
      }
      
      .menu-item:hover {
        color: #f97316 !important;
        transform: translateY(-2px);
      }
      
      .menu-item::after {
        content: '';
        position: absolute;
        bottom: -4px;
        left: 50%;
        transform: translateX(-50%) scaleX(0);
        width: 80%;
        height: 2px;
        background: linear-gradient(90deg, #f97316, #fb923c);
        transition: transform 0.3s ease;
      }
      
      .menu-item:hover::after {
        transform: translateX(-50%) scaleX(1);
      }
      
      /* 드롭다운 메뉴 스타일 */
      .dropdown-menu {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(249, 115, 22, 0.1);
      }
      
      .dropdown-item {
        transition: all 0.2s ease;
      }
      
      .dropdown-item:hover {
        background: linear-gradient(135deg, #f97316 0%, #fb923c 100%) !important;
        color: white !important;
        transform: translateX(4px);
      }
      [x-cloak] { display: none !important; }
      
      /* Hide scrollbar for Chrome, Safari and Opera */
      .no-scrollbar::-webkit-scrollbar {
          display: none;
      }
      /* Hide scrollbar for IE, Edge and Firefox */
      .no-scrollbar {
          -ms-overflow-style: none;  /* IE and Edge */
          scrollbar-width: none;  /* Firefox */
      }

      /* 1단계: 로그인/회원가입 통합 버튼 스타일 */
      .login-btn {
          background: #ff6b00;
          color: white;
          padding: 10px 20px; /* 크기 조절 */
          border-radius: 25px;
          font-weight: bold;
          border: none;
          cursor: pointer;
          font-size: 14px; /* 폰트 사이즈 조절 */
          transition: background 0.3s;
          display: inline-flex;
          align-items: center;
          justify-content: center;
          white-space: nowrap;
      }

      .login-btn:hover {
          background: #e55d00;
      }

      /* 4단계: 사용자 메뉴 및 드롭다운 스타일 */
      .user-menu {
          position: relative;
          display: inline-block;
      }

      /* Hover Gap Bridge */
      .user-menu::after {
          content: '';
          position: absolute;
          top: 100%;
          right: 0;
          width: 300px; /* 드롭다운 너비 커버 */
          height: 20px; /* 마진(8px) 커버 */
          background: transparent;
      }

      .user-btn {
          display: flex;
          align-items: center;
          gap: 8px;
          background: white;
          border: 1px solid #ddd;
          padding: 8px 16px; /* 크기 조정 */
          border-radius: 25px;
          cursor: pointer;
          font-size: 14px;
          transition: all 0.3s;
      }

      .user-btn:hover {
          border-color: #ff6b00;
          background: #fff5f0;
      }

      .quote-text {
          color: #333;
          font-weight: 500;
      }

      .quote-count {
          background: #f0f0f0;
          color: #666;
          padding: 2px 8px;
          border-radius: 10px;
          font-weight: bold;
          font-size: 12px;
      }

      .quote-count.active {
          background: #ff6b00;
          color: white;
      }

      /* 드롭다운 */
      .dropdown-content {
          display: none;
          position: absolute;
          top: 100%;
          right: 0;
          margin-top: 8px;
          background: white;
          border: 1px solid #ddd;
          border-radius: 12px;
          box-shadow: 0 10px 25px rgba(0,0,0,0.1);
          min-width: 220px;
          z-index: 1000;
          overflow: hidden;
      }

      .user-menu:hover .dropdown-content {
          display: block;
      }

      .dropdown-content a {
          display: flex;
          align-items: center;
          justify-content: space-between;
          padding: 12px 20px;
          color: #333;
          text-decoration: none;
          font-size: 14px;
          transition: background 0.2s;
      }

      .dropdown-content a:hover {
          background: #f8f9fa;
          color: #ff6b00;
      }

      .dropdown-content hr {
          margin: 0;
          border: none;
          border-top: 1px solid #eee;
      }

      .dropdown-content .badge {
          background: #ff6b00;
          color: white;
          padding: 2px 6px;
          border-radius: 10px;
          font-size: 11px;
          font-weight: bold;
      }

    </style>
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('mobileMenu', {
            open: false,
            toggle() {
                this.open = ! this.open;
            },
            close() {
                this.open = false;
            }
        });
    });

    // 6단계: 견적 건수 실시간 업데이트
    function updateQuoteCount() {
        fetch('<?php echo G5_URL ?>/api/get_quote_count.php')
            .then(response => response.json())
            .then(data => {
                // 데스크탑 버튼 뱃지
                const countBadge = document.querySelector('.quote-count');
                if(countBadge) {
                    countBadge.textContent = data.count + '건';
                    
                    if(data.count > 0) {
                        countBadge.classList.add('active');
                    } else {
                        countBadge.classList.remove('active');
                    }
                }
                
                // 모바일 버튼 뱃지 (필요 시 선택자 조정)
                // 현재 모바일은 별도 클래스를 사용하지 않으므로 추가 구현 필요할 수 있음
                // 예: document.querySelectorAll('.quote-count').forEach(...)

                // 드롭다운 메뉴 및 모바일 메뉴의 뱃지 업데이트
                // 데스크탑 드롭다운
                const dropdownBadge = document.querySelector('.dropdown-content .badge');
                // 모바일 메뉴
                const mobileBadge = document.querySelector('.bg-orange-500.text-white.text-xs.px-2.py-0.5.rounded-full');

                if(data.count > 0) {
                    // 데스크탑 드롭다운
                    if(dropdownBadge) {
                        dropdownBadge.textContent = data.count;
                    } else {
                        const quoteLink = document.querySelector('.dropdown-content a[href*="quote_history"]');
                        if(quoteLink) {
                            const badge = document.createElement('span');
                            badge.className = 'badge';
                            badge.textContent = data.count;
                            quoteLink.appendChild(badge);
                        }
                    }
                    
                    // 모바일 메뉴 (간단히 텍스트 업데이트만 시도)
                    if(mobileBadge) {
                        mobileBadge.textContent = data.count;
                    }
                } else {
                    if(dropdownBadge) dropdownBadge.remove();
                    // 모바일은 0일 때 '0' 텍스트를 보여주고 있어서 제거보다는 텍스트 변경
                    const mobileZeroObj = document.querySelector('.text-gray-500.text-xs'); // 0 표시
                     // 모바일 로직은 복잡할 수 있으니 일단 데스크탑 위주로 적용
                }
            })
            .catch(error => console.error('Error:', error));
    }

    // 30초마다 자동 업데이트 (로그인 상태일 때만)
    <?php if($is_member): ?>
    if(document.querySelector('.user-menu')) {
        setInterval(updateQuoteCount, 30000);
    }
    <?php endif; ?>

    </script>

    <!-- Desktop Header Layout (Hidden on Mobile) -->
    <div class="hidden lg:flex mx-auto items-center justify-between h-20 px-4 w-full max-w-[1400px]"> <!-- FIX: justify-between, max-width 추가 -->
      <nav class="flex-1">
        <ul class="flex justify-center items-center gap-x-8">
          <?php foreach(array_slice($main_menu, 0, 3) as $item): ?>
            <li class="relative group">
              <?php if ($item['label'] === '간판의뢰'): ?>
                <a href="<?php echo G5_BBS_URL . '/write.php?bo_table=consult'; ?>"
                  class="menu-item text-slate-900 text-lg font-semibold leading-6 transition whitespace-nowrap"
                >
                  <?= $item['label'] ?>
                </a>
              <?php else: ?>
                <a href="<?= $item['href'] ?>"
                   <?php if (!empty($item['target'])) echo 'target="' . $item['target'] . '"'; ?>
                   class="menu-item text-slate-900 text-lg font-semibold leading-6 transition whitespace-nowrap"
                > 
                  <?= $item['label'] ?>
                </a>
              <?php endif; ?>

              <?php if (!empty($item['children'])): ?>
                <ul
                  class="dropdown-menu absolute left-1/2 transform -translate-x-1/2 top-full mt-6 min-w-full rounded-2xl shadow-2xl py-3 px-2 z-20 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all"
                  style="white-space:nowrap"
                >
                  <?php foreach($item['children'] as $child): ?>
                    <li>
                      <a href="<?= $child['href'] ?>"
                         class="dropdown-item block px-4 py-3 text-slate-900 rounded-xl text-base text-center font-medium"
                         <?php if (!empty($child['target'])) echo 'target="' . $child['target'] . '"'; ?>
                      >
                        <?= $child['label'] ?>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </nav>
      
      <!-- Logo (Absolute Center or Flex Center) -->
      <a href="<?php echo G5_URL; ?>/index.php" class="flex-shrink-0 flex items-center mx-4 absolute left-1/2 transform -translate-x-1/2">
        <img src="<?php echo G5_THEME_IMG_URL ?>/logo.png" alt="로고" class="h-10 w-auto" />
      </a>
      
      <!-- Right Side: Menu + User Info -->
      <div class="flex-1 flex justify-end items-center gap-x-6">
          <nav>
            <ul class="flex justify-center items-center gap-x-6"> <!-- gap 줄임 -->
          <?php foreach(array_slice($main_menu, 3) as $item): ?>
            <li class="relative group">
              <a href="<?= $item['href'] ?>"
                 <?php if (!empty($item['target'])) echo 'target="' . $item['target'] . '"'; ?>
                 class="menu-item text-slate-900 text-lg font-semibold leading-6 transition whitespace-nowrap"
              >
                <?= $item['label'] ?>
              </a>

              <?php if (!empty($item['children'])): ?>
                <ul
                  class="dropdown-menu absolute left-1/2 transform -translate-x-1/2 top-full mt-6 min-w-full rounded-2xl shadow-2xl py-3 px-2 z-20 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all"
                  style="white-space:nowrap"
                >
                  <?php foreach($item['children'] as $child): ?>
                    <li>
                      <a href="<?= $child['href'] ?>"
                         class="dropdown-item block px-4 py-3 text-slate-900 rounded-xl text-base text-center font-medium"
                         <?php if (!empty($child['target'])) echo 'target="' . $child['target'] . '"'; ?>
                      >
                        <?= $child['label'] ?>
                      </a>
                    </li>
                  <?php endforeach; ?>
                </ul>
              <?php endif; ?>
            </li>
          <?php endforeach; ?>
        </ul>
      </nav>
      
      <!-- User Menu (Relative Position) -->
      <div class="text-slate-900 text-sm font-medium leading-7 flex items-center flex-shrink-0 z-50">
        <?php if ($is_member): ?>
          <div class="user-menu h-full flex items-center">
              <button class="user-btn" onclick="location.href='<?php echo $quote_menu_url; ?>'">
                  <span class="quote-text"><?php echo $quote_menu_label; ?></span>
                  <?php if($quote_count > 0): ?>
                      <span class="quote-count active"><?=$quote_count?>건</span>
                  <?php else: ?>
                      <span class="quote-count">0건</span>
                  <?php endif; ?>
                  <span style="font-size: 10px; margin-left: 2px;">▼</span>
              </button>
              
              <div class="dropdown-content">
                  <?php if($member['mb_level'] >= 5): ?>
                      <a href="<?php echo G5_THEME_URL ?>/admin_quote.php">🔧 관리자 페이지</a>
                      <hr>
                  <?php endif; ?>
                  
                  <a href="<?php echo $quote_menu_url; ?>">
                      📋 <?php echo $quote_menu_label; ?>
                      <?php if($quote_count > 0): ?>
                          <span class="badge"><?=$quote_count?></span>
                      <?php endif; ?>
                  </a>
                  <a href="<?php echo G5_URL ?>/mypage/usage_history.php">📦 이용 내역</a>
                  <a href="<?php echo G5_URL ?>/mypage/my_page.php">👤 마이페이지</a>
                  <a href="<?php echo G5_URL ?>/mypage/coupon_box.php">🎟️ 쿠폰함</a>
                  <hr>
                  <a href="<?php echo G5_BBS_URL ?>/logout.php">🚪 로그아웃</a>
              </div>
          </div>
        <?php else: ?>
          <button class="login-btn" onclick="toggleLoginModal(); return false;">
              로그인/회원가입
          </button>
        <?php endif; ?>
      </div>

      </div> <!-- Right Side End -->
    </div>

    <!-- Mobile Header Layout (Visible only on Mobile) -->
    <?php if (strpos(basename($_SERVER['PHP_SELF']), 'admin_') !== 0): ?>
    <div class="lg:hidden flex flex-col w-full bg-white" x-data="{ activeMenu: null }">
        <!-- Row 1: Top Bar (Hamburger, Logo, Icons) -->
        <div class="flex items-center justify-between h-14 px-4 border-b border-gray-100">
            <!-- Left: Hamburger -->
            <button class="flex items-center justify-center p-2 -ml-2 text-gray-800" @click="$store.mobileMenu.toggle()">
                <span class="sr-only">메뉴 열기</span>
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                </svg>
            </button>
            
            <!-- Center: Logo -->
            <a href="<?php echo G5_URL; ?>/index.php" class="flex-shrink-0">
                <img src="<?php echo G5_THEME_IMG_URL ?>/logo.png" alt="로고" class="h-6 w-auto" />
            </a>
            
            <!-- Right: Icons -->
            <div class="flex items-center gap-x-3">
                <!-- 진행중인 견적 아이콘 (모바일 추가) -->
                <?php if ($is_member): ?>
                    <a href="<?php echo $quote_menu_url; ?>" class="flex items-center relative text-gray-800 hover:text-orange-600">
                        <span class="sr-only"><?php echo $quote_menu_label; ?></span>
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
                        </svg>
                        <?php if($quote_count > 0): ?>
                            <span class="absolute -top-1 -right-1 bg-orange-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full border border-white">
                                <?=$quote_count?>
                            </span>
                        <?php endif; ?>
                    </a>
                <?php endif; ?>

                <!-- 모바일 로그인/로그아웃 -->
                <?php if (!$is_member): ?>
                    <a href="#" onclick="toggleLoginModal(); return false;" class="text-sm font-medium text-gray-800">
                        <span class="sr-only">로그인</span>
                         <button class="login-btn" style="padding: 6px 12px; font-size: 12px;">로그인</button>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Mobile Horizontal Menu (GNB) -->
        <div class="lg:hidden w-full bg-white border-b border-gray-100" x-data="{ activeGnb: null }">
            <!-- 1st Depth: Main Menu -->
            <div class="overflow-x-auto whitespace-nowrap no-scrollbar relative z-10 bg-white">
                <div class="flex px-4 items-center h-12">
                    <?php foreach($main_menu as $index => $item): ?>
                        <?php if (!empty($item['children'])): ?>
                            <button @click="activeGnb === <?= $index ?> ? activeGnb = null : activeGnb = <?= $index ?>" 
                                    class="flex-shrink-0 mr-6 text-[15px] font-semibold transition-colors duration-200"
                                    :class="activeGnb === <?= $index ?> ? 'text-orange-600' : 'text-gray-800'">
                                <?= $item['label'] ?>
                                <span class="text-[10px] ml-0.5 align-middle opacity-50" :class="activeGnb === <?= $index ?> ? 'rotate-180 inline-block' : 'inline-block'">▼</span>
                            </button>
                        <?php else: ?>
                            <a href="<?= $item['href'] ?>" class="flex-shrink-0 mr-6 text-[15px] font-semibold text-gray-800 hover:text-orange-600 transition-colors duration-200">
                                <?= $item['label'] ?>
                            </a>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 2nd Depth: Sub Menu -->
            <?php foreach($main_menu as $index => $item): ?>
                <?php if (!empty($item['children'])): ?>
                    <div x-show="activeGnb === <?= $index ?>" x-collapse class="bg-gray-50 border-t border-gray-100 overflow-x-auto whitespace-nowrap no-scrollbar" x-cloak>
                        <div class="flex px-4 items-center h-12">
                            <a href="<?= $item['href'] !== '#' ? $item['href'] : $item['children'][0]['href'] ?>" class="flex-shrink-0 mr-5 text-sm font-bold text-gray-900 flex items-center">
                                ALL <svg class="w-3 h-3 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                            </a>
                            <?php foreach($item['children'] as $child): ?>
                                <a href="<?= $child['href'] ?>" class="flex-shrink-0 mr-5 text-sm font-medium text-gray-600 hover:text-orange-600 transition-colors">
                                    <?= $child['label'] ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endforeach; ?>
        </div>

        <!-- Mobile Drawer Menu (Sidebar) -->
        <div x-show="$store.mobileMenu.open" class="relative z-50 lg:hidden" role="dialog" aria-modal="true" x-cloak>
            
            <!-- Backdrop -->
            <div x-show="$store.mobileMenu.open" 
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black/60 backdrop-blur-sm"
                 @click="$store.mobileMenu.close()"></div>

            <!-- Sidebar -->
            <div class="fixed inset-0 flex z-50 pointer-events-none">
                <div x-show="$store.mobileMenu.open" 
                     x-transition:enter="transition ease-in-out duration-300 transform"
                     x-transition:enter-start="-translate-x-full"
                     x-transition:enter-end="translate-x-0"
                     x-transition:leave="transition ease-in-out duration-300 transform"
                     x-transition:leave-start="translate-x-0"
                     x-transition:leave-end="-translate-x-full"
                     class="pointer-events-auto relative w-[80%] max-w-[300px] h-screen bg-white shadow-2xl flex flex-col"
                     x-data="{ drawerActive: null }">
                     
                    <!-- Drawer Header -->
                    <div class="flex items-center justify-between px-5 py-6 bg-gray-900 shrink-0">
                        <div>
                            <?php if ($is_member): ?>
                                <div class="text-white font-bold text-lg"><?php echo $member['mb_nick']; ?>님</div>
                                <div class="text-gray-400 text-xs mt-1">반갑습니다!</div>
                                <div class="mt-4 flex flex-col gap-2">
                                     <a href="<?php echo $quote_menu_url; ?>" class="text-white text-sm flex justify-between items-center bg-gray-800 px-3 py-2 rounded-lg">
                                         <span>📋 <?php echo $quote_menu_label; ?></span>
                                         <?php if($quote_count > 0): ?>
                                            <span class="bg-orange-500 text-white text-xs px-2 py-0.5 rounded-full"><?=$quote_count?></span>
                                         <?php else: ?>
                                            <span class="text-gray-500 text-xs">0</span>
                                         <?php endif; ?>
                                     </a>
                                     <a href="<?php echo G5_URL ?>/mypage/my_page.php" class="text-gray-300 text-sm hover:text-white">👤 마이페이지</a>
                                </div>
                            <?php else: ?>
                                <div class="text-white font-bold text-lg">로그인이 필요합니다.</div>
                                <button onclick="toggleLoginModal(); return false;" class="login-btn mt-2 w-full">로그인/회원가입</button>
                            <?php endif; ?>
                        </div>
                        <button type="button" class="text-gray-400 hover:text-white transition-colors" @click="$store.mobileMenu.close()">
                            <span class="sr-only">메뉴 닫기</span>
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <!-- Drawer Buttons (Moved to Top) -->
                    <div class="p-4 bg-white border-b border-gray-100 shrink-0">
                        <div class="grid grid-cols-<?php echo ($is_member && $member['mb_level'] >= 5) ? '2' : '1'; ?> gap-3">
                            <?php if ($is_member): ?>
                                <a href="<?php echo G5_BBS_URL ?>/logout.php" class="flex items-center justify-center py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-100 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    로그아웃
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($is_member && $member['mb_level'] >= 5): ?>
                            <a href="<?php echo G5_THEME_URL ?>/admin_quote.php" class="flex items-center justify-center py-2.5 text-sm font-medium text-white bg-gray-800 rounded-lg shadow-sm hover:bg-gray-700 transition-colors">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                관리
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Drawer Content -->
                    <div class="flex-1 flex-grow h-full overflow-y-auto bg-white custom-scrollbar min-h-0">
                        <ul class="py-2">
                            <?php if(empty($main_menu)) { echo '<li class="p-4">메뉴 준비중</li>'; } ?>
                            <?php foreach($main_menu as $index => $item): ?>
                                <li class="border-b border-gray-50 last:border-0">
                                    <?php if (!empty($item['children'])): ?>
                                        <button @click="drawerActive === <?= $index ?> ? drawerActive = null : drawerActive = <?= $index ?>"
                                                class="flex justify-between items-center w-full px-5 py-4 text-left group hover:bg-gray-50 transition-colors">
                                            <span class="text-[15px] font-medium text-gray-800 group-hover:text-orange-600 transition-colors"><?= $item['label'] ?></span>
                                            <svg class="w-4 h-4 text-gray-400 transition-transform duration-200" 
                                                 :class="drawerActive === <?= $index ?> ? 'rotate-180 text-orange-500' : ''"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                            </svg>
                                        </button>
                                        <ul x-show="drawerActive === <?= $index ?>" x-collapse class="bg-gray-50 py-2">
                                            <?php foreach($item['children'] as $child): ?>
                                                <li>
                                                    <a href="<?= $child['href'] ?>" class="block px-8 py-2.5 text-sm text-gray-600 hover:text-orange-600 hover:bg-gray-100/50 transition-colors">
                                                        - <?= $child['label'] ?>
                                                    </a>
                                                </li>
                                            <?php endforeach; ?>
                                        </ul>
                                    <?php else: ?>
                                        <a href="<?= $item['href'] ?>" class="block px-5 py-4 text-[15px] font-medium text-gray-800 hover:bg-gray-50 hover:text-orange-600 transition-colors">
                                            <?= $item['label'] ?>
                                        </a>
                                    <?php endif; ?>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>



                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

  </header>
</div>

<div id="wrapper">

    <div id="container_wr">

        <div id="container">
