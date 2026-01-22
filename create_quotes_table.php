<?php
include_once('./_common.php');

// quotes 테이블 생성
$sql = "CREATE TABLE IF NOT EXISTS `quotes` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `mb_id` varchar(20) NOT NULL DEFAULT '',
    `sign_type` varchar(255) NOT NULL DEFAULT '',
    `location` varchar(255) NOT NULL DEFAULT '',
    `width` varchar(50) NOT NULL DEFAULT '',
    `height` varchar(50) NOT NULL DEFAULT '',
    `content` text NOT NULL,
    `status` varchar(50) NOT NULL DEFAULT 'pending',
    `created_at` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `mb_id` (`mb_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;";
sql_query($sql);

// quote_replies 테이블 생성
$sql = "CREATE TABLE IF NOT EXISTS `quote_replies` (
    `id` int(11) NOT NULL AUTO_INCREMENT,
    `quote_id` int(11) NOT NULL DEFAULT 0,
    `mb_id` varchar(20) NOT NULL DEFAULT '',
    `content` text NOT NULL,
    `created_at` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`id`),
    KEY `quote_id` (`quote_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;";
sql_query($sql);

echo "테이블 생성 완료";
?>