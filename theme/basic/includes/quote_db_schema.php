<?php
/**
 * Quote System - Database Schema Initialization
 * 견적 시스템 DB 스키마 초기화
 * 
 * 이 파일은 필요한 테이블들을 생성하고 컬럼을 추가합니다.
 */

if (!defined('_GNUBOARD_'))
    exit;

/**
 * Initialize all quote system tables
 * 모든 견적 시스템 테이블 초기화
 */
function init_quote_tables()
{
    create_store_table();
    create_store_status_log_table();
    create_store_share_link_table();
    create_quote_measure_table();
    create_work_table();
    add_quote_columns();
}

/**
 * Create g5_work table (통합 업무 관리)
 */
function create_work_table()
{
    if (!sql_query(" DESCRIBE g5_work ", false)) {
        $sql = "
            CREATE TABLE IF NOT EXISTS `g5_work` (
              `work_id` int(11) NOT NULL AUTO_INCREMENT,
              `work_subject` varchar(255) NOT NULL DEFAULT '' COMMENT '업무 제목 (견적명/고객명)',
              `work_status` varchar(50) NOT NULL DEFAULT '작성중' COMMENT '진행상태',
              `qa_id` int(11) NOT NULL DEFAULT 0 COMMENT '견적 ID',
              `customer_id` int(11) NOT NULL DEFAULT 0 COMMENT '고객 ID',
              `created_at` datetime NOT NULL,
              `updated_at` datetime NOT NULL,
              PRIMARY KEY (`work_id`),
              INDEX idx_qa_id (qa_id),
              INDEX idx_customer_id (customer_id),
              INDEX idx_created_at (created_at)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ";
        sql_query($sql, true);
    }
}

/**
 * Create g5_store table (지점/고객사 정보)
 */
function create_store_table()
{
    if (!sql_query(" DESCRIBE g5_store ", false)) {
        $sql = "
            CREATE TABLE IF NOT EXISTS `g5_store` (
              `st_id` int(11) NOT NULL AUTO_INCREMENT,
              `st_name` varchar(100) NOT NULL DEFAULT '' COMMENT '지점명(상호)',
              `st_addr` varchar(255) NOT NULL DEFAULT '' COMMENT '주소',
              `st_addr_detail` varchar(255) NOT NULL DEFAULT '' COMMENT '상세주소',
              `st_contact` varchar(50) NOT NULL DEFAULT '' COMMENT '연락처',
              `st_hp` varchar(50) NOT NULL DEFAULT '' COMMENT '핸드폰',
              `st_manager` varchar(50) NOT NULL DEFAULT '' COMMENT '담당자',
              `st_tags` text NOT NULL COMMENT '태그 (쉼표 구분)',
              `st_memo` text NOT NULL COMMENT '메모',
              `st_status` varchar(50) NOT NULL DEFAULT '견적서발송' COMMENT '진행상태',
              `st_datetime` datetime NOT NULL COMMENT '등록일시',
              PRIMARY KEY (`st_id`),
              INDEX idx_st_name (st_name)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ";
        sql_query($sql, true);
    }
}

/**
 * Create g5_store_status_log table (지점 상태 변경 이력)
 */
function create_store_status_log_table()
{
    if (!sql_query(" DESCRIBE g5_store_status_log ", false)) {
        $sql = "
            CREATE TABLE IF NOT EXISTS `g5_store_status_log` (
              `ssl_id` int(11) NOT NULL AUTO_INCREMENT,
              `st_id` int(11) NOT NULL DEFAULT 0,
              `ssl_status_before` varchar(50) NOT NULL DEFAULT '',
              `ssl_status_after` varchar(50) NOT NULL DEFAULT '',
              `ssl_changed_by` varchar(50) NOT NULL DEFAULT '' COMMENT '변경자 (관리자 ID)',
              `ssl_datetime` datetime NOT NULL,
              PRIMARY KEY (`ssl_id`),
              INDEX idx_st_id (st_id)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ";
        sql_query($sql, true);
    }
}

/**
 * Create g5_store_share_link table (지점 공유 링크)
 */
function create_store_share_link_table()
{
    if (!sql_query(" DESCRIBE g5_store_share_link ", false)) {
        $sql = "
            CREATE TABLE IF NOT EXISTS `g5_store_share_link` (
              `slink_id` int(11) NOT NULL AUTO_INCREMENT,
              `st_id` int(11) NOT NULL DEFAULT 0,
              `slink_token` varchar(64) NOT NULL DEFAULT '' COMMENT '공유 토큰',
              `slink_is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '활성화 여부',
              `slink_created_at` datetime NOT NULL,
              PRIMARY KEY (`slink_id`),
              UNIQUE KEY `slink_token` (`slink_token`),
              INDEX idx_st_id (st_id)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ";
        sql_query($sql, true);
    }
}

/**
 * Create g5_quote_measure table (현장측정 데이터)
 */
function create_quote_measure_table()
{
    if (!sql_query(" DESCRIBE g5_quote_measure ", false)) {
        $sql = "
            CREATE TABLE IF NOT EXISTS `g5_quote_measure` (
              `qm_id` int(11) NOT NULL AUTO_INCREMENT,
              `qa_id` int(11) NOT NULL DEFAULT 0 COMMENT '견적 ID',
              `qm_index` int(11) NOT NULL DEFAULT 0 COMMENT '순서',
              `qm_type` varchar(100) NOT NULL DEFAULT '' COMMENT '간판 종류',
              `qm_width` varchar(50) NOT NULL DEFAULT '' COMMENT '가로(W)',
              `qm_height` varchar(50) NOT NULL DEFAULT '' COMMENT '세로(H)',
              `qm_qty` int(11) NOT NULL DEFAULT 0 COMMENT '수량',
              `qm_img1` varchar(255) NOT NULL DEFAULT '',
              `qm_img2` varchar(255) NOT NULL DEFAULT '',
              `qm_memo` text NOT NULL COMMENT '메모',
              PRIMARY KEY (`qm_id`),
              INDEX idx_qa_id (qa_id)
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
        ";
        sql_query($sql, true);
    }
}

/**
 * Add columns to g5_quote table
 * g5_quote 테이블에 필요한 컬럼 추가
 */
function add_quote_columns()
{
    // Add qa_store_id column
    $row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_store_id' ");
    if (!$row) {
        sql_query(" ALTER TABLE g5_quote ADD `qa_store_id` int(11) NOT NULL DEFAULT 0 AFTER `qa_id` ", false);
        sql_query(" ALTER TABLE g5_quote ADD INDEX idx_qa_store_id (qa_store_id) ", false);
    }

    // Add qa_tax_company_name column (Step 1-2-3 Sync)
    $row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_tax_company_name' ");
    if (!$row) {
        sql_query(" ALTER TABLE g5_quote ADD `qa_tax_company_name` varchar(100) NOT NULL DEFAULT '' AFTER `qa_client_name` ", false);
    }

    // Add qa_construct_date column
    $row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_construct_date' ");
    if (!$row) {
        sql_query(" ALTER TABLE g5_quote ADD `qa_construct_date` date NULL AFTER `qa_client_addr2` ", false);
    }

    // Add qa_deposit_status column
    $row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_deposit_status' ");
    if (!$row) {
        sql_query(" ALTER TABLE g5_quote ADD `qa_deposit_status` varchar(20) NOT NULL DEFAULT '입금대기' AFTER `qa_construct_date` ", false);
    }

    // Add qa_payment_method column
    $row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_payment_method' ");
    if (!$row) {
        sql_query(" ALTER TABLE g5_quote ADD `qa_payment_method` varchar(20) NOT NULL DEFAULT '현금' AFTER `qa_deposit_status` ", false);
    }

    // Add qa_tax_yn column
    $row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_tax_yn' ");
    if (!$row) {
        sql_query(" ALTER TABLE g5_quote ADD `qa_tax_yn` char(1) NOT NULL DEFAULT 'N' AFTER `qa_payment_method` ", false);
    }

    // Add qa_tax_biz_num column
    $row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_tax_biz_num' ");
    if (!$row) {
        sql_query(" ALTER TABLE g5_quote ADD `qa_tax_biz_num` varchar(20) NOT NULL DEFAULT '' AFTER `qa_tax_yn` ", false);
    }

    // Add qa_tax_ceo_name column
    $row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_tax_ceo_name' ");
    if (!$row) {
        sql_query(" ALTER TABLE g5_quote ADD `qa_tax_ceo_name` varchar(50) NOT NULL DEFAULT '' AFTER `qa_tax_biz_num` ", false);
    }

    // Add qa_tax_type column
    $row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_tax_type' ");
    if (!$row) {
        sql_query(" ALTER TABLE g5_quote ADD `qa_tax_type` varchar(10) NOT NULL DEFAULT '01' AFTER `qa_tax_ceo_name` ", false);
    }

    // Add qa_tax_claim_type column
    $row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_tax_claim_type' ");
    if (!$row) {
        sql_query(" ALTER TABLE g5_quote ADD `qa_tax_claim_type` varchar(10) NOT NULL DEFAULT '01' AFTER `qa_tax_type` ", false);
    }

    // Add qa_tax_item_name column
    $row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_tax_item_name' ");
    if (!$row) {
        sql_query(" ALTER TABLE g5_quote ADD `qa_tax_item_name` varchar(100) NOT NULL DEFAULT '' AFTER `qa_tax_claim_type` ", false);
    }

    // Add qa_tax_trade_name column
    $row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_tax_trade_name' ");
    if (!$row) {
        sql_query(" ALTER TABLE g5_quote ADD `qa_tax_trade_name` varchar(100) NOT NULL DEFAULT '' AFTER `qa_tax_item_name` ", false);
    }

    // Add qa_tax_addr column
    $row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_tax_addr' ");
    if (!$row) {
        sql_query(" ALTER TABLE g5_quote ADD `qa_tax_addr` varchar(255) NOT NULL DEFAULT '' AFTER `qa_tax_trade_name` ", false);
    }

    // Add qa_tax_email column
    $row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_tax_email' ");
    if (!$row) {
        sql_query(" ALTER TABLE g5_quote ADD `qa_tax_email` varchar(100) NOT NULL DEFAULT '' AFTER `qa_tax_addr` ", false);
    }

    // Add qa_memo_user column (고객 참고사항)
    $row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_memo_user' ");
    if (!$row) {
        sql_query(" ALTER TABLE g5_quote ADD `qa_memo_user` text NOT NULL AFTER `qa_tax_email` ", false);
    }

    // Add qa_status column (진행 상태)
    $row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_status' ");
    if (!$row) {
        sql_query(" ALTER TABLE g5_quote ADD `qa_status` varchar(50) NOT NULL DEFAULT '견적서발송' AFTER `qa_memo_user` ", false);
    }
    // Add qa_tax_condition column (업태)
    $row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_tax_condition' ");
    if (!$row) {
        sql_query(" ALTER TABLE g5_quote ADD `qa_tax_condition` varchar(50) NOT NULL DEFAULT '' AFTER `qa_tax_sector` ", false);
    }

    // Add qa_tax_sector column (종목)
    $row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_tax_sector' ");
    if (!$row) {
        sql_query(" ALTER TABLE g5_quote ADD `qa_tax_sector` varchar(50) NOT NULL DEFAULT '' AFTER `qa_tax_type` ", false); // Position check needed
    }

    // [FIX] Re-ordering or ensuring columns exist if I missed any from previous snippet
    // Actually, let's just make sure we cover the planned columns.
    // qa_customer_id is important.
    $row = sql_fetch(" SHOW COLUMNS FROM g5_quote LIKE 'qa_customer_id' ");
    if (!$row) {
        sql_query(" ALTER TABLE g5_quote ADD `qa_customer_id` int(11) NOT NULL DEFAULT 0 AFTER `qa_store_id` ", false);
        sql_query(" ALTER TABLE g5_quote ADD INDEX idx_qa_customer_id (qa_customer_id) ", false);
    }
}

/**
 * Check if all tables exist
 * 모든 테이블이 존재하는지 확인
 * @return array ['missing' => array, 'exists' => bool]
 */
function check_quote_tables()
{
    $tables = [
        'g5_store',
        'g5_store_status_log',
        'g5_store_share_link',
        'g5_quote_measure'
    ];

    $missing = [];
    foreach ($tables as $table) {
        if (!sql_query(" DESCRIBE $table ", false)) {
            $missing[] = $table;
        }
    }

    return [
        'missing' => $missing,
        'exists' => empty($missing)
    ];
}

/**
 * Get table information
 * 테이블 정보 조회
 * @param string $table_name Table name
 * @return array|false Table info or false
 */
function get_table_info($table_name)
{
    $sql = " SHOW TABLE STATUS LIKE '$table_name' ";
    return sql_fetch($sql);
}

/**
 * Get column information
 * 컬럼 정보 조회
 * @param string $table_name Table name
 * @param string $column_name Column name
 * @return array|false Column info or false
 */
function get_column_info($table_name, $column_name)
{
    $sql = " SHOW COLUMNS FROM $table_name LIKE '$column_name' ";
    return sql_fetch($sql);
}

?>