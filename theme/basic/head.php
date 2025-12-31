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
include_once(G5_THEME_PATH.'/modal_quick_consult.php');
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
    </script>

    <!-- Desktop Header Layout (Hidden on Mobile) -->
    <div class="hidden lg:flex mx-auto items-center justify-center h-20 px-4 pr-40 container relative"> <?php // FIX ?>
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
      
      <a href="<?php echo G5_URL; ?>/index.php" class="flex-shrink-0 flex items-center mx-8">
        <img src="<?php echo G5_THEME_IMG_URL ?>/logo.png" alt="로고" class="h-10 w-auto" />
      </a>
      
      <nav class="flex-1">
        <ul class="flex justify-center items-center gap-x-8">
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
      
      <div class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-900 text-sm font-medium leading-7 <?php echo ($is_member && $is_admin) ? 'flex items-center gap-x-4' : 'flex flex-col items-end'; ?>"> <?php // FIX ?>
        <?php if ($is_member): ?>
          <?php if ($is_admin) { ?>
          <a href="<?php echo G5_THEME_URL ?>/admin_quote.php" class="text-orange-600 font-bold hover:underline">관리자페이지</a><?php // FIX ?>
          <?php } ?>
          <a href="<?php echo G5_BBS_URL ?>/logout.php" class="hover:text-primary">로그아웃</a> <?php // FIX ?>
        <?php else: ?>
          <a href="#" onclick="toggleLoginModal(); return false;">로그인</a>
          <a href="<?php echo G5_BBS_URL ?>/register.php" class="hover:text-primary">회원가입</a>
        <?php endif; ?>
      </div>
    </div>

    <!-- Mobile Header Layout (Visible only on Mobile) -->
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
                <?php if ($is_member): ?>
                    <a href="<?php echo G5_BBS_URL ?>/logout.php" class="text-sm font-medium text-gray-800">로그아웃</a>
                <?php else: ?>
                    <a href="#" onclick="toggleLoginModal(); return false;" class="text-sm font-medium text-gray-800">로그인</a>
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
                            <?php else: ?>
                                <div class="text-white font-bold text-lg">로그인이 필요합니다.</div>
                                <a href="#" onclick="toggleLoginModal(); return false;" class="text-orange-500 text-sm font-medium mt-1 inline-block hover:underline">로그인 하러가기 &rarr;</a>
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
                        <div class="grid grid-cols-<?php echo ($is_member && $is_admin) ? '2' : '1'; ?> gap-3">
                            <?php if ($is_member): ?>
                                <a href="<?php echo G5_BBS_URL ?>/logout.php" class="flex items-center justify-center py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-100 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                                    로그아웃
                                </a>
                            <?php else: ?>
                                <a href="#" onclick="toggleLoginModal(); return false;" class="flex items-center justify-center py-2.5 text-sm font-medium text-gray-600 bg-white border border-gray-200 rounded-lg shadow-sm hover:bg-gray-100 transition-colors">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                                    회원가입
                                </a>
                            <?php endif; ?>
                            
                            <?php if ($is_member && $is_admin): ?>
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

  </header>
</div>

<div id="wrapper">

    <div id="container_wr">

        <div id="container">
