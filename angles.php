<?php
// إعدادات الاتصال (نفس الإعدادات السابقة)
$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "robot_control";

// 1. إنشاء الاتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// 2. التحقق من الاتصال
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// 3. استعلام لجلب آخر (أحدث) صف تم إدخاله
$sql = "SELECT servo1, servo2, servo3, servo4 FROM angles ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    // 4. جلب البيانات
    $row = $result->fetch_assoc();
    
    // 5. طباعة البيانات بتنسيق سهل للقراءة (مثال: 90,120,45,180)
    // الـ ESP سيقرأ هذا السطر ويقوم بتحليله
    echo $row["servo1"] . "," . $row["servo2"] . "," . $row["servo3"] . "," . $row["servo4"];
    
} else {
    // في حال كانت قاعدة البيانات فارغة، اطبع قيم افتراضية
    echo "90,90,90,90";
}

// 6. إغلاق الاتصال
$conn->close();
?>