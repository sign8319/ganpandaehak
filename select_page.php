<?php
include_once('./_common.php');

if (!$is_member || $member['mb_level'] < 5) {
    alert('접근 권한이 없습니다.', G5_URL);
}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>페이지 선택</title>
    <link rel="stylesheet" href="<?php echo G5_THEME_CSS_URL ?>/default.css">
    <style>
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        .select-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 90%;
        }

        .choice-btn {
            display: block;
            width: 100%;
            max-width: 300px;
            padding: 20px;
            margin: 15px auto;
            border: 2px solid #ddd;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            color: #333;
            background: #fff;
        }

        .choice-btn:hover {
            border-color: #ff6b00;
            background: #fff5f0;
            transform: translateY(-2px);
        }

        .icon {
            font-size: 40px;
            margin-bottom: 10px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            margin: 5px 0;
        }

        .desc {
            color: #666;
            font-size: 13px;
        }

        h2 {
            margin-bottom: 30px;
            font-size: 24px;
            color: #333;
        }
    </style>
</head>

<body>
    <div class="modal-overlay">
        <div class="select-box">
            <h2>어디로 이동하시겠어요?</h2>

            <!-- 관리자 페이지 경로: 테마 내 관리자 페이지로 추정되어 연결 -->
            <a href="<?php echo G5_THEME_URL ?>/admin_quote.php" class="choice-btn">
                <div class="icon">🔧</div>
                <div class="title">관리자 페이지</div>
                <div class="desc">견적/고객/포트폴리오 관리</div>
            </a>

            <a href="<?php echo G5_URL ?>" class="choice-btn">
                <div class="icon">🏠</div>
                <div class="title">홈페이지</div>
                <div class="desc">일반 페이지 둘러보기</div>
            </a>
        </div>
    </div>
</body>

</html>