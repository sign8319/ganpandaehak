<?php
include_once('./_common.php');

// Admin Check
if (!$is_admin) {
    alert('관리자만 접근 가능합니다.', G5_URL);
}

$page_title = "견적 계산기 관리";
include_once(G5_THEME_PATH . '/head.php');

// DB 테이블 생성 (최초 실행시)
$create_tables = "
CREATE TABLE IF NOT EXISTS `g5_quote_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_key` varchar(50) NOT NULL,
  `name` varchar(100) NOT NULL,
  `icon` varchar(255) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`),
  UNIQUE KEY `category_key` (`category_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `g5_quote_subcategories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `g5_quote_products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `subcategory_id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `unit_price` decimal(10,2) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `calc_type` enum('area','text','length','fixed') NOT NULL,
  `min_area` decimal(10,2) DEFAULT NULL,
  `base_size` int(11) DEFAULT NULL,
  `description` text,
  `sort_order` int(11) DEFAULT 0,
  `is_active` tinyint(1) DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `g5_quote_options` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `price` decimal(10,2) DEFAULT 0,
  `discount` decimal(5,4) DEFAULT NULL,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS `g5_quote_history` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `transaction_count` int(11) DEFAULT 0,
  `avg_spec` varchar(100) DEFAULT NULL,
  `avg_price` decimal(10,2) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

// 초기 데이터 삽입 (최초 실행시)
$insert_initial = "
INSERT IGNORE INTO `g5_quote_categories` (`category_key`, `name`, `icon`, `sort_order`) VALUES
('channel', '채널', '🔤', 1),
('flex', '플렉스/천갈이', '🏴', 2),
('frame', '프레임', '⬜', 3),
('print', '실사출력', '🖨️', 4),
('awning', '어닝', '🏪', 5),
('parts', '부자재', '🔧', 6);
";

?>

<div class="w-full min-h-screen bg-gray-50/50 flex items-stretch">
    <!-- Sidebar -->
    <?php include_once(G5_THEME_PATH . '/admin_project_sidebar.php'); ?>

    <div class="flex-1 min-w-0 p-4 lg:p-8">

        <!-- Header -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">견적 계산기 관리</h1>
            <p class="text-sm text-gray-500 mt-1">카테고리, 제품, 옵션을 추가/수정/삭제할 수 있습니다</p>
        </div>

        <!-- Tabs -->
        <div class="mb-6 border-b border-gray-200">
            <nav class="flex space-x-4">
                <button onclick="showTab('categories')" id="tab-categories"
                    class="admin-tab active py-3 px-4 text-sm font-medium border-b-2 border-blue-600 text-blue-600">
                    카테고리 관리
                </button>
                <button onclick="showTab('products')" id="tab-products"
                    class="admin-tab py-3 px-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                    제품 관리
                </button>
                <button onclick="showTab('options')" id="tab-options"
                    class="admin-tab py-3 px-4 text-sm font-medium border-b-2 border-transparent text-gray-500 hover:text-gray-700">
                    옵션 관리
                </button>
            </nav>
        </div>

        <!-- Tab Content -->
        <div id="content-categories" class="tab-content">
            <?php include 'admin_quote_categories.php'; ?>
        </div>

        <div id="content-products" class="tab-content hidden">
            <?php include 'admin_quote_products.php'; ?>
        </div>

        <div id="content-options" class="tab-content hidden">
            <?php include 'admin_quote_options.php'; ?>
        </div>

    </div>
</div>

<style>
    .admin-tab.active {
        border-color: #2563eb;
        color: #2563eb;
    }

    .tab-content {
        animation: fadeIn 0.3s;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }
</style>

<script>
    function showTab(tab) {
        // Hide all tabs
        document.querySelectorAll('.tab-content').forEach(el => el.classList.add('hidden'));
        document.querySelectorAll('.admin-tab').forEach(el => el.classList.remove('active'));

        // Show selected tab
        document.getElementById('content-' + tab).classList.remove('hidden');
        document.getElementById('tab-' + tab).classList.add('active');
    }
</script>

<?php
include_once(G5_THEME_PATH . '/tail.php');
?>