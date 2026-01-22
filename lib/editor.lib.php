<?php
if (!defined('_GNUBOARD_'))
    exit; // 개별 페이지 접근 불가

/*
    환경설정에서 에디터 선택이 없는 경우에 사용하는 라이브러리 입니다.
    에디터 선택시 "선택없음"이 아닌 경우 plugin/editor 하위 디렉토리의 각 에디터이름/editor.lib.php 를 수정하시기 바랍니다.
*/

if (!function_exists('editor_html')) {
    function editor_html($id, $content, $is_dhtml_editor = false) // FIX
    {
        return "<textarea id=\"$id\" name=\"$id\" style=\"width:100%;\" maxlength=\"65536\">$content</textarea>";
    }
}

// textarea 로 값을 넘긴다. javascript 반드시 필요
if (!function_exists('get_editor_js')) {
    function get_editor_js($id, $is_dhtml_editor = false) // FIX
    {
        // submit 함수 안/밖 어디서 호출돼도 안전하게 DOM에서 다시 잡게 함 // FIX
        return "var {$id}_editor = document.getElementById('{$id}');\n"; // FIX
    }
}

// textarea 의 값이 비어 있는지 검사
if (!function_exists('chk_editor_js')) {
    function chk_editor_js($id, $is_dhtml_editor = false) // FIX
    {
        // element 존재 체크 추가(없는 경우에도 에러/오판 방지) // FIX
        return "var {$id}_editor = document.getElementById('{$id}');\n" // FIX
            . "if (typeof oEditors !== 'undefined') { try { oEditors.getById['{$id}'].exec('UPDATE_CONTENTS_FIELD', []); } catch(e) {} }\n" // Sync SmartEditor
            . "if (!{$id}_editor) { return true; }\n"; // Skip if element missing
        //. "if (!{$id}_editor || !{$id}_editor.value) { alert(\"내용을 입력해 주십시오.\"); if({$id}_editor) {$id}_editor.focus(); return false; }\n"; // Disable strict check
    }
}
