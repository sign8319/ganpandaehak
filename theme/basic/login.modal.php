<?php
if (!defined('_GNUBOARD_'))
    exit; // 개별 페이지 접근 불가
?>

<!-- Login Modal -->
<div id="loginModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm hidden"
    style="display: none;">
    <div
        class="login_modal_box bg-white rounded-3xl p-10 w-full max-w-[450px] shadow-2xl relative overflow-hidden animate-fade-in-up mx-4">

        <!-- Close Button -->
        <button onclick="closeLoginModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 p-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <!-- Social Login Section -->
        <div id="socialLogin">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 mb-3 tracking-tight leading-snug">간판대학이<br>간판 비용을 줄여드릴게요.
                </h2>
                <p class="text-sm text-gray-500 font-medium break-keep">원활한 상담 및 견적 알림을 위해 로그인이 필요해요.</p>
            </div>

            <?php
            // 카카오 로그인 URL 생성
            $kakao_api_key = "639f97a24765411c8a65368faaaf7fd4";
            $kakao_redirect_uri = urlencode("https://간판대학.com/kakao_callback.php");
            $kakao_url = "https://kauth.kakao.com/oauth/authorize?client_id={$kakao_api_key}&redirect_uri={$kakao_redirect_uri}&response_type=code";

            // 네이버 로그인 URL 생성
            $naver_client_id = "zr5sckoxHr_mSrE1979d";
            $naver_redirect_uri = urlencode("https://간판대학.com/naver_callback.php");
            $naver_url = "https://nid.naver.com/oauth2.0/authorize?client_id={$naver_client_id}&redirect_uri={$naver_redirect_uri}&response_type=code&state=SIGN_UNIV";
            ?>

            <div class="flex flex-col gap-3 mb-6">
                <a href="<?php echo $kakao_url; ?>"
                    class="flex items-center justify-center w-full h-14 rounded-xl bg-[#FEE500] hover:bg-[#FDD835] transition-all relative group shadow-sm text-black font-bold text-base gap-2">
                    <i class="fa-solid fa-comment text-xl"></i> 카카오로 계속하기
                </a>

                <a href="<?php echo $naver_url; ?>"
                    class="flex items-center justify-center w-full h-14 rounded-xl bg-[#03C75A] hover:bg-[#02B651] transition-all relative group shadow-sm text-white font-bold text-base gap-2">
                    <span class="text-xl font-black">N</span> 네이버로 계속하기
                </a>
            </div>

            <a href="#"
                class="block text-center mt-6 text-gray-400 text-xs hover:text-gray-600 underline-offset-2 hover:underline"
                onclick="showAdminLogin(); return false;">
                관리자로그인
            </a>
        </div>

        <!-- Admin Login Section (Hidden by default) -->
        <div id="adminLogin" style="display:none;">
            <div class="text-center mb-8">
                <h2 class="text-2xl font-bold text-gray-900 tracking-tight">관리자 로그인</h2>
            </div>

            <form action="<?php echo G5_HTTPS_BBS_URL ?>/login_check.php" onsubmit="return flogin_modal_submit(this);"
                method="post" class="flex flex-col gap-4">
                <input type="hidden" name="url" value="<?php echo G5_URL ?>/login_dispatch.php">

                <div>
                    <label for="login_modal_id" class="sr-only">아이디</label>
                    <input type="text" name="mb_id" id="login_modal_id"
                        class="w-full h-12 px-4 bg-gray-50 rounded-xl border border-gray-200 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 text-sm transition-shadow outline-none"
                        placeholder="아이디" required>
                </div>
                <div>
                    <label for="login_modal_pw" class="sr-only">비밀번호</label>
                    <input type="password" name="mb_password" id="login_modal_pw"
                        class="w-full h-12 px-4 bg-gray-50 rounded-xl border border-gray-200 focus:border-orange-500 focus:ring-1 focus:ring-orange-500 text-sm transition-shadow outline-none"
                        placeholder="비밀번호" required>
                </div>

                <button type="submit"
                    class="w-full h-12 mt-2 rounded-xl bg-gray-900 text-white font-bold text-sm hover:bg-black transition-colors">
                    로그인
                </button>
            </form>

            <a href="#" class="block text-center mt-6 text-gray-500 text-sm hover:text-gray-800"
                onclick="showSocialLogin(); return false;">
                ← 뒤로가기
            </a>
        </div>

    </div>
</div>

<style>
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translate3d(0, 20px, 0);
        }

        to {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.4s ease-out forwards;
    }
</style>

<script>
    function showAdminLogin() {
        document.getElementById('socialLogin').style.display = 'none';
        document.getElementById('adminLogin').style.display = 'block';
    }

    function showSocialLogin() {
        document.getElementById('adminLogin').style.display = 'none';
        document.getElementById('socialLogin').style.display = 'block';
    }

    function toggleLoginModal() {
        // Alias for openLoginModal logic or toggle
        const modal = document.getElementById('loginModal');
        if (!modal) return;

        if (modal.style.display === 'none' || modal.classList.contains('hidden')) {
            openLoginModal();
        } else {
            closeLoginModal();
        }
    }

    function openLoginModal() {
        const modal = document.getElementById('loginModal');
        if (!modal) return;
        modal.style.display = 'flex';
        modal.classList.remove('hidden');
        showSocialLogin(); // Always start with social login
    }

    function closeLoginModal() {
        const modal = document.getElementById('loginModal');
        if (!modal) return;
        modal.style.display = 'none';
        modal.classList.add('hidden');
    }

    function flogin_modal_submit(f) {
        if (!f.mb_id.value) {
            alert('아이디를 입력하세요.');
            f.mb_id.focus();
            return false;
        }
        if (!f.mb_password.value) {
            alert('비밀번호를 입력하세요.');
            f.mb_password.focus();
            return false;
        }
        return true;
    }
</script>