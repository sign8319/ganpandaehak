<?php
include_once('./_common.php');

// 페이지 제목
$g5['title'] = '견적 신청 완료';

include_once(G5_THEME_PATH . '/head.php');
?>

<style>
    .completion-container {
        max-width: 600px;
        margin: 80px auto 60px;
        padding: 40px 20px;
        text-align: center;
    }

    /* SVG 체크 아이콘 애니메이션 */
    .success-icon {
        width: 120px;
        height: 120px;
        margin: 0 auto 30px;
    }

    .success-icon svg {
        width: 100%;
        height: 100%;
    }

    .circle {
        stroke-dasharray: 283;
        stroke-dashoffset: 283;
        animation: drawCircle 0.6s ease-out forwards;
    }

    .checkmark {
        stroke-dasharray: 60;
        stroke-dashoffset: 60;
        animation: drawCheck 0.4s ease-out 0.6s forwards;
    }

    @keyframes drawCircle {
        to {
            stroke-dashoffset: 0;
        }
    }

    @keyframes drawCheck {
        to {
            stroke-dashoffset: 0;
        }
    }

    /* 제목 스타일 */
    .completion-title {
        font-size: 45px;
        font-weight: 800;
        color: #1a1a1a;
        margin: 0 0 25px 0;
        line-height: 1.2;
    }

    /* 본문 스타일 */
    .completion-message {
        font-size: 16px;
        color: #666;
        margin: 0 0 50px 0;
        line-height: 1.6;
    }

    .completion-message strong {
        color: #f97316;
        font-weight: 700;
    }

    /* 전화번호 영역 */
    .contact-info {
        margin: 0 0 50px 0;
    }

    .contact-label {
        font-size: 14px;
        color: #999;
        margin-bottom: 10px;
        font-weight: 500;
    }

    .phone {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 32px;
        font-weight: 800;
        color: #f97316;
        text-decoration: none;
        margin: 0;
        transition: all 0.2s;
    }

    .phone:hover {
        color: #ea580c;
        transform: scale(1.03);
    }

    .phone i {
        font-size: 28px;
    }

    /* 버튼 그룹 */
    .button-group {
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 14px 28px;
        border-radius: 12px;
        font-size: 15px;
        font-weight: 600;
        text-decoration: none;
        transition: all 0.3s;
        border: none;
    }

    .btn-primary {
        background: #1a1a1a;
        color: white;
    }

    .btn-primary:hover {
        background: #2a2a2a;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        color: white;
    }

    .btn-secondary {
        background: white;
        color: #1a1a1a;
        border: 2px solid #e5e5e5;
    }

    .btn-secondary:hover {
        border-color: #f97316;
        color: #f97316;
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(249, 115, 22, 0.1);
    }

    /* 모바일 반응형 */
    @media (max-width: 768px) {
        .completion-container {
            padding: 30px 15px;
            margin: 50px auto 40px;
        }

        .success-icon {
            width: 100px;
            height: 100px;
            margin-bottom: 25px;
        }

        .completion-title {
            font-size: 36px;
            margin-bottom: 20px;
        }

        .completion-message {
            font-size: 15px;
            margin-bottom: 40px;
        }

        .contact-info {
            margin-bottom: 40px;
        }

        .phone {
            font-size: 28px;
        }

        .phone i {
            font-size: 24px;
        }

        .button-group {
            flex-direction: column;
            gap: 10px;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="completion-container">
    <!-- SVG 체크 아이콘 -->
    <div class="success-icon">
        <svg viewBox="0 0 100 100">
            <circle class="circle" cx="50" cy="50" r="45" fill="none" stroke="#f97316" stroke-width="6" />
            <path class="checkmark" fill="none" stroke="#f97316" stroke-width="6" stroke-linecap="round"
                d="M 30 50 L 45 65 L 70 35" />
        </svg>
    </div>

    <h1 class="completion-title">견적 신청이<br>완료되었습니다!</h1>

    <p class="completion-message">
        접수 완료!<br>
        <strong>지금 바로 검토 중</strong>입니다.
    </p>

    <div class="contact-info">
        <div class="contact-label">지금 바로 전화주세요!</div>
        <a href="tel:1600-8319" class="phone">
            <i class="fa fa-phone"></i>
            1600-8319
        </a>
    </div>

    <div class="button-group">
        <a href="<?php echo G5_URL; ?>" class="btn btn-primary">
            <i class="fa fa-home"></i>
            메인으로 돌아가기
        </a>
        <?php if ($is_member) { ?>
            <a href="<?php echo G5_URL; ?>/mypage/quote_history.php" class="btn btn-secondary">
                <i class="fa fa-list-alt"></i>
                내 견적내역 확인
            </a>
        <?php } else { ?>
            <a href="https://blog.naver.com/sign8319" target="_blank" class="btn btn-secondary">
                <i class="fa fa-external-link"></i>
                블로그 구경하기
            </a>
        <?php } ?>
    </div>
</div>

<?php
include_once(G5_THEME_PATH . '/tail.php');
?>