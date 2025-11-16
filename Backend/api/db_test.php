<?php
// 開啟最詳細的錯誤報告
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

echo "<h1>資料庫連線測試</h1>";

// 檢查 database.php 檔案是否存在
$config_path = __DIR__ . '/../config/database.php';
echo "<p>正在讀取設定檔: " . $config_path . "</p>";

if (!file_exists($config_path)) {
    echo "<p style='color: red; font-weight: bold;'>錯誤：找不到 database.php 檔案！</p>";
    exit;
}

require_once $config_path;

echo "<p>嘗試連線到資料庫...</p>";

try {
    $database = new Database();
    $db = $database->getConnection();

    if ($db) {
        echo "<p style='color: green; font-weight: bold;'>資料庫連線成功！</p>";
        echo "<p>伺服器版本: " . $db->server_info . "</p>";
        $db->close();
    } else {
        // getConnection() 應該會 die()，但以防萬一
        echo "<p style='color: red; font-weight: bold;'>錯誤：getConnection() 沒有返回有效的連線物件。</p>";
    }

} catch (Exception $e) {
    echo "<p style='color: red; font-weight: bold;'>捕捉到例外錯誤: " . $e->getMessage() . "</p>";
}
?>