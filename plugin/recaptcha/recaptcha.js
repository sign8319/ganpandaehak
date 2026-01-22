function chk_captcha()
{
	// 여러 방법으로 recaptcha response 확인
	var recaptchaResponse = document.getElementById('g-recaptcha-response');
	var grecaptcha = window.grecaptcha;
	
	// 방법 1: hidden input 확인
	if (recaptchaResponse && recaptchaResponse.value) {
		return true;
	}
	
	// 방법 2: grecaptcha 객체 확인
	if (grecaptcha && grecaptcha.getResponse && grecaptcha.getResponse().length > 0) {
		return true;
	}
	
	// 방법 3: 모든 g-recaptcha-response 요소 확인
	var allResponses = document.querySelectorAll('[name="g-recaptcha-response"]');
	for (var i = 0; i < allResponses.length; i++) {
		if (allResponses[i].value) {
			return true;
		}
	}
	
	alert("자동등록방지를 반드시 체크해 주세요.");
	return false;
}