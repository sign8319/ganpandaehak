<?php
include_once('./_common.php');

if (!$is_member) {
    die('error: login required');
}

$w = $_POST['w'] ? $_POST['w'] : $_GET['w'];

if ($w == 'biz') {
    // 사업자 정보 업데이트
    $mb_biz_name = strip_tags($_POST['mb_biz_name']);
    $mb_biz_num = strip_tags($_POST['mb_biz_num']);
    $mb_biz_rep = strip_tags($_POST['mb_biz_rep']);
    $mb_biz_addr = strip_tags($_POST['mb_biz_addr']);
    $mb_biz_type = strip_tags($_POST['mb_biz_type']);

    $sql = " update {$g5['member_table']} 
                set mb_biz_name = '{$mb_biz_name}',
                    mb_biz_num = '{$mb_biz_num}',
                    mb_biz_rep = '{$mb_biz_rep}',
                    mb_biz_addr = '{$mb_biz_addr}',
                    mb_biz_type = '{$mb_biz_type}'
                where mb_id = '{$member['mb_id']}' ";
    sql_query($sql);

    alert('사업자 정보가 저장되었습니다.', './my_page.php');

} else if ($w == 'ad_add') {
    // 주소지 추가
    $ad_subject = strip_tags($_POST['ad_subject']);
    $ad_name = strip_tags($_POST['ad_name']);
    $ad_hp = strip_tags($_POST['ad_hp']);
    $ad_zip = strip_tags($_POST['ad_zip']);
    $ad_addr1 = strip_tags($_POST['ad_addr1']);
    $ad_addr2 = strip_tags($_POST['ad_addr2']);
    $ad_default = (int) $_POST['ad_default'];

    if ($ad_default) {
        sql_query(" update g5_member_address set ad_default = 0 where mb_id = '{$member['mb_id']}' ");
    }

    $sql = " insert into g5_member_address
                set mb_id = '{$member['mb_id']}',
                    ad_subject = '{$ad_subject}',
                    ad_name = '{$ad_name}',
                    ad_hp = '{$ad_hp}',
                    ad_zip = '{$ad_zip}',
                    ad_addr1 = '{$ad_addr1}',
                    ad_addr2 = '{$ad_addr2}',
                    ad_default = '{$ad_default}' ";
    sql_query($sql);

    alert('주소지가 추가되었습니다.', './my_page.php');

} else if ($w == 'ad_del') {
    // 주소지 삭제
    $ad_id = (int) $_GET['ad_id'];
    sql_query(" delete from g5_member_address where ad_id = '{$ad_id}' and mb_id = '{$member['mb_id']}' ");

    alert('주소지가 삭제되었습니다.', './my_page.php');
}

goto_url('./my_page.php');
?>