<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가
include_once(G5_LIB_PATH.'/thumbnail.lib.php');

// 스타일 시트 추가
add_stylesheet('<link rel="stylesheet" href="'.$board_skin_url.'/style.css">', 0);
?>

<script src="https://unpkg.com/isotope-layout@3/dist/isotope.pkgd.min.js"></script>
<script src="https://unpkg.com/imagesloaded@5/imagesloaded.pkgd.min.js"></script>

<style>
/* 폰트 및 기본 설정 */
#bo_gall { font-family: 'Noto Sans KR', sans-serif; }

/* 1. 필터 버튼 디자인 */
.portfolio-filter { 
    text-align:center; 
    margin-bottom: 40px; 
    margin-top: 60px; 
}
.portfolio-filter button { 
    display:inline-block; margin:0 4px; padding:10px 24px; 
    border:1px solid #e0e0e0; background:#fff; border-radius:30px; 
    cursor:pointer; transition: all 0.3s ease; 
    font-size: 15px; font-weight: 600; color:#666;
    box-shadow: 0 2px 5px rgba(0,0,0,0.03);
}
.portfolio-filter button:hover { 
    background:#f5f5f5; color:#333; transform: translateY(-2px);
}
.portfolio-filter button.is-checked { 
    background:#222; color:#fff; border-color:#222; 
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

/* 2. 그리드 레이아웃 (여백 만들기 전략) */
#bo_gall .gall_row { 
    width: 100%; 
    margin: 0 auto; 
    padding: 0; 
    /* 양쪽 여백 보정을 위한 마이너스 마진 */
    margin-left: -15px; 
    width: calc(100% + 30px);
}

#bo_gall .gall_li { 
    width: 25%; /* PC: 정확히 4등분 */
    padding: 15px; /* ★ 핵심: 사진 사이의 간격(Gap)을 만듭니다 */
    float: left; 
    box-sizing: border-box; 
    border: none; 
    background: transparent; /* 배경 투명하게 */
    box-shadow: none; /* 그림자 제거 (내부 박스로 이동) */
}

/* ★ 실제 눈에 보이는 카드 디자인 (내부 박스) */
#bo_gall .gall_box { 
    background: #fff;
    padding: 0; 
    border-radius: 20px; /* 둥근 모서리 */
    overflow: hidden; 
    box-shadow: 0 10px 30px rgba(0,0,0,0.08); /* 부드러운 그림자 */
    transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1); 
    height: 100%;
}

/* 마우스 올렸을 때 효과 */
#bo_gall .gall_li:hover .gall_box {
    transform: translateY(-10px); /* 위로 둥실 떠오름 */
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

/* 3. 이미지 영역 */
#bo_gall .gall_img { 
    overflow: hidden; 
    border-radius: 0; 
    position: relative;
}

#bo_gall .gall_img img { 
    width: 100%; 
    height: auto; 
    display: block; 
    transition: transform 0.5s ease; 
}

/* 이미지 확대 효과 */
#bo_gall .gall_li:hover .gall_img img {
    transform: scale(1.1); 
}

/* 4. 텍스트 영역 */
#bo_gall .gall_text_href { 
    padding: 25px; /* 텍스트 여백을 좀 더 넓게 */
    background: #fff;
    text-align: left;
}

#bo_gall .bo_tit { 
    font-size: 18px; 
    font-weight: 700; 
    display:block; 
    margin-bottom:8px; 
    text-decoration:none; 
    color:#111; 
    line-height: 1.4;
    word-break: keep-all; 
}

#bo_gall .bo_cate_link { 
    font-size: 13px; 
    color: #888; 
    display:inline-block; 
    margin-bottom:10px; 
    font-weight: 500;
    letter-spacing: -0.5px;
}

/* 모바일 반응형 */
@media (max-width: 768px) {
    #bo_gall .gall_li { 
        width: 50%; /* 모바일 2열 */
        padding: 8px; /* 모바일은 간격을 조금 좁게 */
    } 
    #bo_gall .gall_row { 
        margin-left: -8px; 
        width: calc(100% + 16px);
    }
    .portfolio-filter { margin-top: 30px; margin-bottom: 20px; }
    .portfolio-filter button { padding: 8px 16px; font-size: 13px; margin: 2px; }
    #bo_gall .gall_text_href { padding: 15px; } /* 모바일 텍스트 여백 축소 */
    #bo_gall .bo_tit { font-size: 15px; }
}
</style>

<div id="bo_gall" style="width:<?php echo $width; ?>">

    <div class="portfolio-filter button-group js-radio-button-group">
        <button class="button is-checked" data-filter="*">전체보기</button>
        <?php if ($is_category) { 
            // 카테고리 문자열을 파싱하여 버튼으로 만듦 (간단 버전)
            $cate_list = explode("|", $board['bo_category_list']);
            foreach ($cate_list as $cate) {
                echo '<button class="button" data-filter=".'.bin2hex($cate).'">'.$cate.'</button>';
            }
        } ?>
    </div>

    <div id="bo_btn_top">
        <div id="bo_list_total">
            <span>Total <?php echo number_format($total_count) ?>건</span>
            <?php echo $page ?> 페이지
        </div>
        <?php if ($write_href) { ?>
        <ul class="btn_bo_user">
            <li><a href="<?php echo $write_href ?>" class="btn_b01 btn" title="글쓰기"><i class="fa fa-pencil" aria-hidden="true"></i> 글쓰기</a></li>
        </ul>
        <?php } ?>
    </div>

    <ul id="gall_ul" class="gall_row">
        <?php for ($i=0; $i<count($list); $i++) {
            
            // 카테고리명을 클래스로 변환 (필터링용)
            $cate_class = $list[$i]['ca_name'] ? bin2hex($list[$i]['ca_name']) : ''; 
        ?>
        
        <li class="gall_li item <?php echo $cate_class; ?>">
            <div class="gall_box">
                <div class="gall_con">
                    <div class="gall_img">
                        <a href="<?php echo $list[$i]['href'] ?>">
                        <?php
                        // 썸네일 생성 (width만 지정하고 height는 0으로 주어 원본 비율 유지)
                        $thumb = get_list_thumbnail($board['bo_table'], $list[$i]['wr_id'], $board['bo_gallery_width'], 0, false, true);

                        if($thumb['src']) {
                            echo '<img src="'.$thumb['src'].'" alt="'.$thumb['alt'].'">';
                        } else {
                            echo '<span class="no_image">no image</span>';
                        }
                        ?>
                        </a>
                    </div>
                    
                    <div class="gall_text_href">
                        <?php if ($is_category && $list[$i]['ca_name']) { ?>
                        <span class="bo_cate_link"><?php echo $list[$i]['ca_name'] ?></span>
                        <?php } ?>
                        
                        <a href="<?php echo $list[$i]['href'] ?>" class="bo_tit">
                            <?php echo $list[$i]['subject'] ?>
                            <?php if ($list[$i]['icon_new']) echo "<span class=\"new_icon\">N</span>"; ?>
                        </a>
                    </div>
                </div>
            </div>
        </li>
        <?php } ?>
        
        <?php if (count($list) == 0) { echo "<li class=\"empty_list\">게시물이 없습니다.</li>"; } ?>
    </ul>
    
    <?php echo $write_pages; ?>
    
    <div class="bo_sch_wrap" style="margin-top:20px; text-align:center;">   
        <form name="fsearch" method="get">
        <input type="hidden" name="bo_table" value="<?php echo $bo_table ?>">
        <input type="hidden" name="sca" value="<?php echo $sca ?>">
        <input type="hidden" name="sop" value="and">
        <select name="sfl" id="sfl">
            <?php echo get_board_sfl_select_options($sfl); ?>
        </select>
        <input type="text" name="stx" value="<?php echo stripslashes($stx) ?>" required id="stx" class="sch_input" size="25" maxlength="20" placeholder="검색어 입력">
        <button type="submit" value="검색" class="sch_btn"><i class="fa fa-search" aria-hidden="true"></i> 검색</button>
        </form>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.isotope/3.0.6/isotope.pkgd.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.imagesloaded/5.0.0/imagesloaded.pkgd.min.js"></script>

<script>
jQuery(document).ready(function($){
    
    var $grid = $('#gall_ul');

    // 1. Isotope 실행 (핀터레스트 레이아웃 적용)
    $grid.isotope({
        itemSelector: '.gall_li',
        percentPosition: true,
        masonry: {
            columnWidth: '.gall_li',
            gutter: 0
        }
    });

    // 2. 이미지가 로딩되면 레이아웃 다시 잡기 (겹침 방지)
    $grid.imagesLoaded().progress( function() {
        $grid.isotope('layout');
    });

    // 3. 필터 버튼 클릭 시 동작
    $('.portfolio-filter').on( 'click', 'button', function() {
        var filterValue = $(this).attr('data-filter');
        $grid.isotope({ filter: filterValue });
        
        // 버튼 활성화 스타일 변경
        $('.portfolio-filter button').removeClass('is-checked');
        $(this).addClass('is-checked');
    });
    
    // 4. 안전장치: 0.5초 뒤에 한 번 더 정렬
    setTimeout(function(){
        $grid.isotope('layout');
    }, 500);
});
</script>