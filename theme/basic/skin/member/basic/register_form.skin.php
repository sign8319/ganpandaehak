<?php
if (!defined('_GNUBOARD_'))
	exit; // 개별 페이지 접근 불가

// add_stylesheet('css 구문', 출력순서); 숫자가 작을 수록 먼저 출력됨
add_stylesheet('<link rel="stylesheet" href="' . $member_skin_url . '/style.css">', 0);
add_javascript('<script src="' . G5_JS_URL . '/jquery.register_form.js"></script>', 0);
if ($config['cf_cert_use'] && ($config['cf_cert_simple'] || $config['cf_cert_ipin'] || $config['cf_cert_hp']))
	add_javascript('<script src="' . G5_JS_URL . '/certify.js?v=' . G5_JS_VER . '"></script>', 0);
?>

<!-- 회원정보 입력/수정 시작 { -->

<style>
	.register-container {
		background-color: #F5F5F5;
		padding: 40px 20px;
		min-height: 100vh;
	}

	.register-card {
		max-width: 600px;
		margin: 0 auto;
		background: #FFF;
		border-radius: 16px;
		box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
		overflow: hidden;
	}

	.register-header {
		padding: 32px 32px 0;
		text-align: center;
	}

	.register-header h1 {
		font-size: 24px;
		font-weight: 800;
		color: #1e293b;
		margin-bottom: 8px;
	}

	.register-header p {
		color: #64748b;
		font-size: 14px;
	}

	.register-body {
		padding: 32px;
	}

	.form-section {
		margin-bottom: 32px;
	}

	.form-section-title {
		font-size: 16px;
		font-weight: 700;
		color: #334155;
		margin-bottom: 16px;
		padding-bottom: 8px;
		border-bottom: 1px solid #f1f5f9;
	}

	.form-group {
		margin-bottom: 20px;
	}

	.form-group label {
		display: block;
		font-size: 14px;
		font-weight: 600;
		color: #475569;
		margin-bottom: 8px;
	}

	.form-control {
		width: 100%;
		height: 48px;
		padding: 0 16px;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		font-size: 15px;
		transition: all 0.2s;
		background: #fff;
		box-sizing: border-box;
	}

	.form-control:focus {
		outline: none;
		border-color: #ff6b00;
		box-shadow: 0 0 0 3px rgba(255, 107, 0, 0.1);
	}

	.form-control:read-only {
		background: #f8fafc;
		color: #94a3b8;
	}

	.btn-submit {
		width: 100%;
		height: 56px;
		background: #ff6b00;
		color: #fff;
		border: none;
		border-radius: 12px;
		font-size: 17px;
		font-weight: 700;
		cursor: pointer;
		transition: all 0.2s;
		margin-top: 12px;
	}

	.btn-submit:hover {
		background: #e66000;
		transform: translateY(-1px);
	}

	.register-footer {
		padding: 24px;
		text-align: center;
		border-top: 1px solid #f1f5f9;
	}

	.leave-link {
		color: #94a3b8;
		font-size: 13px;
		text-decoration: none;
		transition: color 0.2s;
	}

	.leave-link:hover {
		color: #64748b;
		text-decoration: underline;
	}

	.flex-row {
		display: flex;
		gap: 12px;
	}

	.flex-grow {
		flex-grow: 1;
	}

	.tooltip-btn {
		background: none;
		border: none;
		color: #94a3b8;
		cursor: pointer;
		padding: 0 4px;
	}

	.msg-info {
		display: block;
		font-size: 12px;
		margin-top: 6px;
		color: #64748b;
	}

	.chk-group {
		display: flex;
		align-items: center;
		gap: 8px;
		margin-bottom: 12px;
		cursor: pointer;
	}

	.chk-group input {
		width: 18px;
		height: 18px;
		border-radius: 4px;
		cursor: pointer;
		accent-color: #ff6b00;
	}

	.chk-group span {
		font-size: 14px;
		color: #475569;
	}

	.btn-action {
		padding: 0 12px;
		height: 48px;
		background: #f1f5f9;
		border: 1px solid #e2e8f0;
		border-radius: 8px;
		font-size: 14px;
		font-weight: 600;
		color: #475569;
		cursor: pointer;
		white-space: nowrap;
	}

	.btn-action:hover {
		background: #e2e8f0;
	}

	@media (max-width: 640px) {
		.register-container {
			padding: 0;
			background: #fff;
		}

		.register-card {
			box-shadow: none;
			border-radius: 0;
		}

		.register-header {
			padding: 40px 20px 0;
		}

		.register-body {
			padding: 24px 20px;
		}
	}
</style>

<div class="register-container">
	<div class="register-card">
		<form id="fregisterform" name="fregisterform" action="<?php echo $register_action_url ?>"
			onsubmit="return fregisterform_submit(this);" method="post" enctype="multipart/form-data"
			autocomplete="off">
			<input type="hidden" name="w" value="<?php echo $w ?>">
			<input type="hidden" name="url" value="<?php echo $urlencode ?>">
			<input type="hidden" name="agree" value="<?php echo $agree ?>">
			<input type="hidden" name="agree2" value="<?php echo $agree2 ?>">
			<input type="hidden" name="cert_type" value="<?php echo $member['mb_certify']; ?>">
			<input type="hidden" name="cert_no" value="">
			<?php if (isset($member['mb_sex'])) { ?><input type="hidden" name="mb_sex"
					value="<?php echo $member['mb_sex'] ?>"><?php } ?>
			<?php if (isset($member['mb_nick_date']) && $member['mb_nick_date'] > date("Y-m-d", G5_SERVER_TIME - ($config['cf_nick_modify'] * 86400))) { ?>
				<input type="hidden" name="mb_nick_default" value="<?php echo get_text($member['mb_nick']) ?>">
				<input type="hidden" name="mb_nick" value="<?php echo get_text($member['mb_nick']) ?>">
			<?php } ?>

			<div class="register-header">
				<h1><?php echo $w == '' ? '회원가입' : '정보 수정'; ?></h1>
				<p><?php echo $w == '' ? '간판대학의 가족이 되어주세요!' : '나의 소중한 정보를 안전하게 관리하세요.'; ?></p>
			</div>

			<div class="register-body">
				<div class="form-section">
					<div class="form-section-title">계정 정보</div>

					<div class="form-group">
						<label for="reg_mb_id">계정 아이디</label>
						<input type="text" name="mb_id" value="<?php echo $member['mb_id'] ?>" id="reg_mb_id" <?php echo $required ?> <?php echo $readonly ?> class="form-control" minlength="3" maxlength="20"
							placeholder="아이디">
						<span id="msg_mb_id" class="msg-info"></span>
					</div>

					<?php if (!$member['mb_social_id']) { ?>
						<div class="form-group">
							<label for="reg_mb_password">비밀번호</label>
							<input type="password" name="mb_password" id="reg_mb_password" <?php echo $required ?>
								class="form-control" minlength="3" maxlength="20" placeholder="비밀번호">
						</div>
						<div class="form-group">
							<label for="reg_mb_password_re">비밀번호 확인</label>
							<input type="password" name="mb_password_re" id="reg_mb_password_re" <?php echo $required ?>
								class="form-control" minlength="3" maxlength="20" placeholder="비밀번호 확인">
						</div>
					<?php } ?>
				</div>

				<div class="form-section">
					<div class="form-section-title">개인 정보</div>

					<?php if ($config['cf_cert_use']) { ?>
						<div class="form-group">
							<label>본인 확인</label>
							<div class="flex-row">
								<?php if ($config['cf_cert_simple']) { ?>
									<button type="button" class="btn-action flex-grow win_sa_cert" data-type="">간편인증</button>
								<?php } ?>
								<?php if ($config['cf_cert_hp']) { ?>
									<button type="button" id="win_hp_cert" class="btn-action flex-grow">휴대폰 인증</button>
								<?php } ?>
							</div>
							<?php if ($member['mb_certify']) {
								$mb_cert = "";
								if ($member['mb_certify'] == 'simple')
									$mb_cert = "간편인증";
								else if ($member['mb_certify'] == 'ipin')
									$mb_cert = "아이핀";
								else if ($member['mb_certify'] == 'hp')
									$mb_cert = "휴대폰";
								?>
								<span class="msg-info" style="color: #10b981; font-weight: 600;">✓ <?php echo $mb_cert; ?> 인증
									완료</span>
							<?php } ?>
						</div>
					<?php } ?>

					<div class="form-group">
						<label for="reg_mb_name">실명</label>
						<input type="text" id="reg_mb_name" name="mb_name"
							value="<?php echo get_text($member['mb_name']) ?>" <?php echo $required ?> <?php echo $readonly; ?> class="form-control" placeholder="이름">
					</div>

					<?php if ($req_nick) { ?>
						<div class="form-group">
							<label for="reg_mb_nick">닉네임</label>
							<input type="hidden" name="mb_nick_default"
								value="<?php echo isset($member['mb_nick']) ? get_text($member['mb_nick']) : ''; ?>">
							<input type="text" name="mb_nick"
								value="<?php echo isset($member['mb_nick']) ? get_text($member['mb_nick']) : ''; ?>"
								id="reg_mb_nick" required class="form-control" maxlength="20" placeholder="닉네임">
							<span id="msg_mb_nick" class="msg-info"></span>
						</div>
					<?php } ?>

					<div class="form-group">
						<label for="reg_mb_email">이메일 주소</label>
						<input type="hidden" name="old_email" value="<?php echo $member['mb_email'] ?>">
						<input type="text" name="mb_email"
							value="<?php echo isset($member['mb_email']) ? $member['mb_email'] : ''; ?>"
							id="reg_mb_email" required class="form-control" maxlength="100" placeholder="E-mail">
					</div>

					<?php if ($config['cf_use_hp'] || ($config["cf_cert_use"] && ($config['cf_cert_hp'] || $config['cf_cert_simple']))) { ?>
						<div class="form-group">
							<label for="reg_mb_hp">휴대폰 번호</label>
							<input type="text" name="mb_hp" value="<?php echo get_text($member['mb_hp']) ?>" id="reg_mb_hp"
								<?php echo $hp_required; ?> 	<?php echo $hp_readonly; ?> class="form-control"
								placeholder="010-0000-0000">
							<?php if ($config['cf_cert_use'] && ($config['cf_cert_hp'] || $config['cf_cert_simple'])) { ?>
								<input type="hidden" name="old_mb_hp" value="<?php echo get_text($member['mb_hp']) ?>">
							<?php } ?>
						</div>
					<?php } ?>

					<?php if ($config['cf_use_addr']) { ?>
						<div class="form-group">
							<label>연락처 주소</label>
							<div class="flex-row" style="margin-bottom: 8px;">
								<input type="text" name="mb_zip"
									value="<?php echo $member['mb_zip1'] . $member['mb_zip2']; ?>" id="reg_mb_zip" <?php echo $config['cf_req_addr'] ? "required" : ""; ?> class="form-control flex-grow"
									maxlength="6" placeholder="우편번호" readonly>
								<button type="button" class="btn-action"
									onclick="win_zip('fregisterform', 'mb_zip', 'mb_addr1', 'mb_addr2', 'mb_addr3', 'mb_addr_jibeon');">주소
									찾기</button>
							</div>
							<input type="text" name="mb_addr1" value="<?php echo get_text($member['mb_addr1']) ?>"
								id="reg_mb_addr1" class="form-control" style="margin-bottom: 8px;" placeholder="기본주소"
								readonly>
							<input type="text" name="mb_addr2" value="<?php echo get_text($member['mb_addr2']) ?>"
								id="reg_mb_addr2" class="form-control" style="margin-bottom: 8px;" placeholder="상세주소">
							<input type="text" name="mb_addr3" value="<?php echo get_text($member['mb_addr3']) ?>"
								id="reg_mb_addr3" class="form-control" readonly placeholder="참고항목">
							<input type="hidden" name="mb_addr_jibeon"
								value="<?php echo get_text($member['mb_addr_jibeon']); ?>">
						</div>
					<?php } ?>
				</div>

				<div class="form-section" style="margin-bottom: 20px;">
					<div class="form-section-title">알림 설정</div>
					<label class="chk-group" for="reg_mb_mailling">
						<input type="checkbox" name="mb_mailling" value="1" id="reg_mb_mailling" <?php echo ($w == '' || $member['mb_mailling']) ? 'checked' : ''; ?>>
						<span>중요 정보 및 혜택 메일을 받겠습니다.</span>
					</label>

					<?php if ($config['cf_use_hp']) { ?>
						<label class="chk-group" for="reg_mb_sms">
							<input type="checkbox" name="mb_sms" value="1" id="reg_mb_sms" <?php echo ($w == '' || $member['mb_sms']) ? 'checked' : ''; ?>>
							<span>진행 현황 등을 문자로 받겠습니다.</span>
						</label>
					<?php } ?>
				</div>

				<?php if ($w == '') { ?>
					<div class="form-group is_captcha_use">
						<label>자동 가입 방지</label>
						<?php echo captcha_html(); ?>
					</div>
				<?php } ?>

				<button type="submit" id="btn_submit"
					class="btn-submit"><?php echo $w == '' ? '회원가입 완료' : '정보 수정하기'; ?></button>
			</div>

			<?php if ($w == 'u') { ?>
				<div class="register-footer">
					<a href="javascript:void(0);" onclick="member_leave();" class="leave-link">간판대학을 탈퇴하시겠어요?</a>
				</div>
			<?php } ?>
		</form>
	</div>
</div>

<script>
	function member_leave() {
		if (confirm("정말 탈퇴하시겠습니까?\n탈퇴 시 모든 정보와 혜택이 사라지며 복구할 수 없습니다.")) {
			location.href = "<?php echo G5_BBS_URL; ?>/member_confirm.php?url=member_leave.php";
		}
	}

	$(function () {
		// 입력창 포커스 효과는 CSS로 처리됨
		// 툴팁 등은 리뉴얼된 UI에서는 제거하거나 보완
		var pageTypeParam = "pageType=register";

		<?php if ($config['cf_cert_use'] && $config['cf_cert_simple']) { ?>
			// 이니시스 간편인증
			var url = "<?php echo G5_INICERT_URL; ?>/ini_request.php";
			var type = "";
			var params = "";
			var request_url = "";

			$(".win_sa_cert").click(function () {
				if (!cert_confirm()) return false;
				type = $(this).data("type");
				params = "?directAgency=" + type + "&" + pageTypeParam;
				request_url = url + params;
				call_sa(request_url);
			});
		<?php } ?>
		<?php if ($config['cf_cert_use'] && $config['cf_cert_ipin']) { ?>
			// 아이핀인증
			var params = "";
			$("#win_ipin_cert").click(function () {
				if (!cert_confirm()) return false;
				params = "?" + pageTypeParam;
				var url = "<?php echo G5_OKNAME_URL; ?>/ipin1.php" + params;
				certify_win_open('kcb-ipin', url);
				return;
			});

		<?php } ?>
		<?php if ($config['cf_cert_use'] && $config['cf_cert_hp']) { ?>
			// 휴대폰인증
			var params = "";
			$("#win_hp_cert").click(function () {
				if (!cert_confirm()) return false;
				params = "?" + pageTypeParam;
				<?php
				switch ($config['cf_cert_hp']) {
					case 'kcb':
						$cert_url = G5_OKNAME_URL . '/hpcert1.php';
						$cert_type = 'kcb-hp';
						break;
					case 'kcp':
						$cert_url = G5_KCPCERT_URL . '/kcpcert_form.php';
						$cert_type = 'kcp-hp';
						break;
					case 'lg':
						$cert_url = G5_LGXPAY_URL . '/AuthOnlyReq.php';
						$cert_type = 'lg-hp';
						break;
					default:
						echo 'alert("기본환경설정에서 휴대폰 본인확인 설정을 해주십시오");';
						echo 'return false;';
						break;
				}
				?>

				certify_win_open("<?php echo $cert_type; ?>", "<?php echo $cert_url; ?>" + params);
				return;
			});
		<?php } ?>
	});

	// submit 최종 폼체크
	function fregisterform_submit(f) {
		// 회원아이디 검사
		if (f.w.value == "") {
			var msg = reg_mb_id_check();
			if (msg) {
				alert(msg);
				f.mb_id.select();
				return false;
			}
		}

		if (f.w.value == "") {
			if (f.mb_password.value.length < 3) {
				alert("비밀번호를 3글자 이상 입력하십시오.");
				f.mb_password.focus();
				return false;
			}
		}

		if (f.mb_password) {
			if (f.mb_password.value != f.mb_password_re.value) {
				alert("비밀번호가 같지 않습니다.");
				f.mb_password_re.focus();
				return false;
			}

			if (f.mb_password.value.length > 0) {
				if (f.mb_password_re.value.length < 3) {
					alert("비밀번호를 3글자 이상 입력하십시오.");
					f.mb_password_re.focus();
					return false;
				}
			}
		}

		// 이름 검사
		if (f.w.value == "") {
			if (f.mb_name.value.length < 1) {
				alert("이름을 입력하십시오.");
				f.mb_name.focus();
				return false;
			}

			/*
			var pattern = /([^가-힣\x20])/i;
			if (pattern.test(f.mb_name.value)) {
				alert("이름은 한글로 입력하십시오.");
				f.mb_name.select();
				return false;
			}
			*/
		}

		<?php if ($w == '' && $config['cf_cert_use'] && $config['cf_cert_req']) { ?>
			// 본인확인 체크
			if (f.cert_no.value == "") {
				alert("회원가입을 위해서는 본인확인을 해주셔야 합니다.");
				return false;
			}
		<?php } ?>

		// 닉네임 검사
		if ((f.w.value == "") || (f.w.value == "u" && f.mb_nick.defaultValue != f.mb_nick.value)) {
			var msg = reg_mb_nick_check();
			if (msg) {
				alert(msg);
				f.reg_mb_nick.select();
				return false;
			}
		}

		// E-mail 검사
		if ((f.w.value == "") || (f.w.value == "u" && f.mb_email.defaultValue != f.mb_email.value)) {
			var msg = reg_mb_email_check();
			if (msg) {
				alert(msg);
				f.reg_mb_email.select();
				return false;
			}
		}

		<?php if (($config['cf_use_hp'] || $config['cf_cert_hp']) && $config['cf_req_hp']) { ?>
			// 휴대폰번호 체크
			var msg = reg_mb_hp_check();
			if (msg) {
				alert(msg);
				f.reg_mb_hp.select();
				return false;
			}
		<?php } ?>

		if (typeof f.mb_icon != "undefined") {
			if (f.mb_icon.value) {
				if (!f.mb_icon.value.toLowerCase().match(/.(gif|jpe?g|png)$/i)) {
					alert("회원아이콘이 이미지 파일이 아닙니다.");
					f.mb_icon.focus();
					return false;
				}
			}
		}

		if (typeof f.mb_img != "undefined") {
			if (f.mb_img.value) {
				if (!f.mb_img.value.toLowerCase().match(/.(gif|jpe?g|png)$/i)) {
					alert("회원이미지가 이미지 파일이 아닙니다.");
					f.mb_img.focus();
					return false;
				}
			}
		}

		if (typeof (f.mb_recommend) != "undefined" && f.mb_recommend.value) {
			if (f.mb_id.value == f.mb_recommend.value) {
				alert("본인을 추천할 수 없습니다.");
				f.mb_recommend.focus();
				return false;
			}

			var msg = reg_mb_recommend_check();
			if (msg) {
				alert(msg);
				f.mb_recommend.select();
				return false;
			}
		}

		<?php echo chk_captcha_js(); ?>

		document.getElementById("btn_submit").disabled = "disabled";

		return true;
	}

	jQuery(function ($) {
		//tooltip
		$(document).on("click", ".tooltip_icon", function (e) {
			$(this).next(".tooltip").fadeIn(400).css("display", "inline-block");
		}).on("mouseout", ".tooltip_icon", function (e) {
			$(this).next(".tooltip").fadeOut();
		});
	});

</script>

<!-- } 회원정보 입력/수정 끝 -->