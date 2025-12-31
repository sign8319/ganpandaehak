<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

// 기본 에디터 라이브러리 파일을 포함
include_once(G5_LIB_PATH.'/editor.lib.php');

// smarteditor2 검증 함수를 완전히 비활성화
function chk_editor_js($id, $is_dhtml_editor=true)
{
    // smarteditor2의 기본 검증을 완전히 비활성화
    return "// smarteditor2 검증 비활성화됨\n";
}

// 추가: smarteditor2 초기화 시 검증 우회
function smarteditor2_init_override()
{
    return "
    <script>
    // smarteditor2 초기화 전에 검증 함수를 미리 오버라이드
    if (typeof window.chk_editor_js !== 'undefined') {
        window.chk_editor_js = function(id, is_dhtml_editor) {
            return '// smarteditor2 검증 비활성화됨\\n';
        };
    }
    
    // smarteditor2 초기화 후 추가 처리
    if (typeof oEditors !== 'undefined') {
        for (var editorId in oEditors.getById) {
            var editor = oEditors.getById[editorId];
            if (editor && editor.getIR) {
                var originalGetIR = editor.getIR;
                editor.getIR = function() {
                    var content = originalGetIR.call(this);
                    var cleanContent = content.replace(/<p[^>]*>\\s*<\\/p>/g, '').replace(/<p[^>]*>\\s*<br[^>]*>\\s*<\\/p>/g, '').replace(/<p[^>]*>\\s*&nbsp;\\s*<\\/p>/g, '').trim();
                    return cleanContent === '' ? '' : content;
                };
            }
        }
    }
    </script>
    ";
} 