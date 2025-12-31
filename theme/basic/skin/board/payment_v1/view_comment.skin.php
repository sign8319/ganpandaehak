<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
?>

<script>
// 글자수 제한
var char_min = parseInt(<?php echo $comment_min ?>); // 최소
var char_max = parseInt(<?php echo $comment_max ?>); // 최대
</script>

<section id="bo_vc" class="mt-16 pt-10 border-t border-gray-200">
    <h2 class="text-xl font-bold mb-6">댓글 <span class="text-brand-orange"><?php echo $view['wr_comment']; ?></span></h2>
    
    <?php
    $cmt_amt = count($list);
    for ($i=0; $i<$cmt_amt; $i++) {
        $comment_id = $list[$i]['wr_id'];
        $cmt_depth = strlen($list[$i]['wr_comment_reply']) * 20;
        $comment = $list[$i]['content'];
        $comment = preg_replace("/\[\<a\s.*href\=\"(http|https|ftp|mms)\:\/\/([^[:space:]]+)\.(mp3|wma|wmv|asf|asx|mpg|mpeg)\".*\<\/a\>\]/i", "<script>doc_write(obj_movie('$1://$2.$3'));</script>", $comment);
    ?>
    
    <article id="c_<?php echo $comment_id ?>" class="py-4 border-b border-gray-100" style="margin-left:<?php echo $cmt_depth ?>px;">
        <div class="flex justify-between items-start mb-2">
            <div class="font-bold text-gray-900"><?php echo $list[$i]['name'] ?></div>
            <div class="text-sm text-gray-400"><i class="fa fa-clock-o"></i> <?php echo date('Y.m.d H:i', strtotime($list[$i]['datetime'])) ?></div>
        </div>
        
        <div class="text-gray-600 text-sm leading-relaxed">
            <?php if (strstr($list[$i]['wr_option'], "secret")) { ?><i class="fa fa-lock text-orange-500"></i> <?php } ?>
            <?php echo $comment ?>
        </div>

        <div class="mt-2 text-right">
            <?php if ($list[$i]['is_reply']) { ?><a href="<?php echo $c_reply_href; ?>" onclick="comment_box('<?php echo $comment_id ?>', 'c'); return false;" class="text-xs text-gray-500 hover:text-brand-orange mr-2">답변</a><?php } ?>
            <?php if ($list[$i]['is_edit']) { ?><a href="<?php echo $c_edit_href; ?>" onclick="comment_box('<?php echo $comment_id ?>', 'cu'); return false;" class="text-xs text-gray-500 hover:text-brand-orange mr-2">수정</a><?php } ?>
            <?php if ($list[$i]['is_del']) { ?><a href="<?php echo $list[$i]['del_link']; ?>" onclick="return comment_delete();" class="text-xs text-gray-500 hover:text-red-500">삭제</a><?php } ?>
        </div>
    </article>
    <?php } ?>
    
    <?php if ($i == 0) { //댓글이 없다면 ?><p class="py-10 text-center text-gray-400 text-sm">등록된 댓글이 없습니다.</p><?php } ?>
</section>
<?php if ($is_comment_write) {
    if($w == '') $w = 'c';
?>
<aside id="bo_vc_w" class="mt-8 p-6 bg-gray-50 rounded-xl border border-gray-100">
    <form name="fviewcomment" id="fviewcomment" action="<?php echo $comment_action_url; ?>" onsubmit="return fviewcomment_submit(this);" method="post" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w ?>" id="w">
    <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
    <input type="hidden" name="wr_id" value="<?php echo $wr_id ?>">
    <input type="hidden" name="comment_id" value="<?php echo $c_id ?>" id="comment_id">
    <input type="hidden" name="sca" value="<?php echo $sca ?>">
    <input type="hidden" name="sfl" value="<?php echo $sfl ?>">
    <input type="hidden" name="stx" value="<?php echo $stx ?>">
    <input type="hidden" name="spt" value="<?php echo $spt ?>">
    <input type="hidden" name="page" value="<?php echo $page ?>">
    <input type="hidden" name="is_good" value="">

    <div class="flex gap-2">
        <textarea id="wr_content" name="wr_content" maxlength="10000" required class="w-full p-3 border border-gray-300 rounded-lg focus:outline-none focus:border-brand-orange text-sm" rows="3" placeholder="댓글 내용을 입력해주세요"></textarea>
        <button type="submit" id="btn_submit" class="w-24 bg-brand-dark text-white font-bold rounded-lg hover:bg-black transition-colors">등록</button>
    </div>
    </form>
</aside>

<script>
var save_before = '';
var save_html = document.getElementById('bo_vc_w').innerHTML;

function fviewcomment_submit(f)
{
    var pattern = /(^\s*)|(\s*$)/g; // \s 공백 문자
    f.is_good.value = 0;
    
    // 내용 검사 등 기본 로직 유지
    if (!f.wr_content.value) {
        alert("내용을 입력하셔야 합니다.");
        return false;
    }
    
    set_comment_token(f);
    document.getElementById("btn_submit").disabled = "disabled";
    return true;
}

function comment_box(comment_id, work)
{
    var el_id,
        form_el = 'fviewcomment',
        respond = document.getElementById(form_el);

    if (comment_id) {
        if (work == 'c') el_id = 'reply_' + comment_id;
        else el_id = 'edit_' + comment_id;
    } else {
        el_id = 'bo_vc_w';
    }

    if (save_before != el_id) {
        if (save_before) {
            document.getElementById(save_before).style.display = 'none';
        }
        document.getElementById(el_id).style.display = '';
        document.getElementById(el_id).appendChild(respond);
        document.getElementById('wr_content').value = '';
        document.getElementById('comment_id').value = comment_id;
        document.getElementById('w').value = work;
        save_before = el_id;
    }
}

function comment_delete()
{
    return confirm("이 댓글을 삭제하시겠습니까?");
}
</script>
<?php } ?>