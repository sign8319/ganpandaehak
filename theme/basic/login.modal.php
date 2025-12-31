<?php
if (!defined('_GNUBOARD_'))
    exit; // 개별 페이지 접근 불가
?>

<!-- Login Modal (Hidden by default) -->
<div id="loginModal"
    class="fixed inset-0 z-[9999] flex items-center justify-center bg-black/60 backdrop-blur-sm hidden text-left"
    style="display: none;"> <!-- display:none for safety -->
    <div class="absolute inset-0" onclick="toggleLoginModal()"></div> <!-- Backdrop Click to Close -->

    <div
        class="login_modal_box bg-white rounded-3xl p-10 w-full max-w-[480px] shadow-2xl relative overflow-hidden animate-fade-in-up mx-4">

        <!-- Close Button -->
        <button onclick="toggleLoginModal()" class="absolute top-4 right-4 text-gray-400 hover:text-gray-600 p-2">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>

        <!-- Background Decoration -->
        <div class="absolute top-0 left-0 w-full h-2 bg-orange-500"></div>

        <div class="text-center mb-8">
            <h1 class="text-2xl font-bold text-gray-900 mb-3 tracking-tight">간판대학이<br>간판 비용을 줄여드릴게요.</h1>
            <p class="text-sm text-gray-500 font-medium break-keep">원활한 상담 및 견적 알림을 위해 로그인이 필요해요.</p>
        </div>

        <!-- Social Login Buttons -->
        <div class="flex flex-col gap-3 mb-8">
            <a href="https://kauth.kakao.com/oauth/authorize?client_id=639f97a24765411c8a65368faaaf7fd4&redirect_uri=https://간판대학.com/kakao_callback.php&response_type=code"
                class="flex items-center justify-center w-full h-14 rounded-full bg-[#FEE500] hover:bg-[#FDD835] transition-all relative group shadow-sm hover:shadow-md">
                <span class="absolute left-6 text-[#3C1E1E] text-xl"><i class="fa-solid fa-comment"></i></span>
                <span class="text-[#3C1E1E] font-bold text-base">카카오로 계속하기</span>
            </a>

            <a href="https://nid.naver.com/oauth2.0/authorize?client_id=zr5sckoxHr_mSrE1979d&redirect_uri=https://간판대학.com/naver_callback.php&response_type=code&state=SIGN_UNIV"
                class="flex items-center justify-center w-full h-14 rounded-full bg-[#03C75A] hover:bg-[#02B651] transition-all relative group shadow-sm hover:shadow-md">
                <span class="absolute left-6 text-white text-xl font-black">N</span>
                <span class="text-white font-bold text-base">네이버로 계속하기</span>
            </a>
        </div>

        <!-- Divider -->
        <div class="relative mb-8 flex items-center justify-center">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-gray-200"></div>
            </div>
            <div class="relative bg-white px-4">
                <span class="text-xs text-gray-400">또는 아이디 로그인</span>
            </div>
        </div>

        <!-- Login Form -->
        <form name="flogin_modal" action="<?php echo G5_HTTPS_BBS_URL ?>/login_check.php"
            onsubmit="return flogin_modal_submit(this);" method="post" class="flex flex-col gap-4">
            <input type="hidden" name="url" value="<?php echo $_SERVER['REQUEST_URI'] ?>">

            <div>
                <label for="login_modal_id" class="sr-only">아이디</label>
                <input type="text" name="mb_id" id="login_modal_id" required
                    class="w-full h-12 px-4 bg-gray-50 rounded-xl border-none focus:ring-2 focus:ring-orange-500 text-sm transition-shadow"
                    placeholder="아이디">
            </div>

            <div>
                <label for="login_modal_pw" class="sr-only">비밀번호</label>
                <input type="password" name="mb_password" id="login_modal_pw" required
                    class="w-full h-12 px-4 bg-gray-50 rounded-xl border-none focus:ring-2 focus:ring-orange-500 text-sm transition-shadow"
                    placeholder="비밀번호">
            </div>

            <div class="flex items-center justify-between text-xs text-gray-400 mt-1">
                <div class="flex items-center gap-1">
                    <input type="checkbox" name="auto_login" id="login_modal_auto_login"
                        class="rounded border-gray-300 text-orange-500 focus:ring-orange-500 w-4 h-4">
                    <label for="login_modal_auto_login">자동로그인</label>
                </div>
                <a href="<?php echo G5_BBS_URL ?>/password_lost.php"
                    class="hover:text-gray-600 underline-offset-2 hover:underline">ID/PW 찾기</a>
            </div>

            <button type="submit"
                class="w-full h-12 mt-2 rounded-xl bg-gray-900 text-white font-bold text-sm hover:bg-black transition-colors">
                로그인
            </button>
            <div class="mt-4 text-center">
                <a href="<?php echo G5_BBS_URL ?>/register.php"
                    class="text-sm text-gray-500 underline hover:text-gray-800">
                    아직 회원이 아니신가요?
                </a>
            </div>
        </form>
    </div>
</div>

<style>
    /* Animation for Modal */
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
    function toggleLoginModal() {
        const modal = document.getElementById('loginModal');
        if (!modal) return;

        if (modal.style.display === 'none' || modal.classList.contains('hidden')) {
            modal.style.display = 'flex';
            modal.classList.remove('hidden');
            // Prevent body scroll?
            // document.body.style.overflow = 'hidden';
        } else {
            modal.style.display = 'none';
            modal.classList.add('hidden');
            // document.body.style.overflow = '';
        }
    }

    jQuery(function ($) {
        $("#login_modal_auto_login").click(function () {
            if (this.checked) {
                this.checked = confirm("자동로그인을 사용하시면 다음부터 회원아이디와 비밀번호를 입력하실 필요가 없습니다.\n\n공공장소에서는 개인정보가 유출될 수 있으니 사용을 자제하여 주십시오.\n\n자동로그인을 사용하시겠습니까?");
            }
        });
    });

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