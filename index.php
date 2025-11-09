<?php
// متغيرات الاتصال بقاعدة البيانات
$servername = "localhost";
$username = "root";
$password = "root"; 
$dbname = "robot_control";

$message = ""; // لرسائل النجاح أو الخطأ

// 1. إنشاء الاتصال (للحفظ أو الحذف)
$conn = new mysqli($servername, $username, $password, $dbname);

// 2. التحقق من نجاح الاتصال
if ($conn->connect_error) {
    die("فشل الاتصال: " . $conn->connect_error);
}

// 3. التحقق من (حفظ) الوضعية
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['save_position'])) {
    
    // استلام البيانات من النموذج
    $servo1 = $_POST['servo1'];
    $servo2 = $_POST['servo2'];
    $servo3 = $_POST['servo3'];
    $servo4 = $_POST['servo4'];
    
    // تجهيز استعلام SQL
    $stmt = $conn->prepare("INSERT INTO angles (servo1, servo2, servo3, servo4) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("iiii", $servo1, $servo2, $servo3, $servo4);
    
    if ($stmt->execute()) {
        $message = "<div class='alert alert-success'>✓ تم حفظ الوضعية بنجاح!</div>";
    } else {
        $message = "<div class='alert alert-error'>✗ خطأ: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// 4. التحقق من (حذف) وضعية
if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_GET['delete'])) {
    $id_to_delete = $_GET['delete'];
    
    $stmt = $conn->prepare("DELETE FROM angles WHERE id = ?");
    $stmt->bind_param("i", $id_to_delete);
    
    if ($stmt->execute()) {
        $message = "<div class='alert alert-warning'>✓ تم حذف الوضعية.</div>";
        // لإعادة التوجيه لنفس الصفحة بدون بارامتر الحذف
        header("Location: index.php");
        exit;
    } else {
        $message = "<div class='alert alert-error'>✗ خطأ: " . $stmt->error . "</div>";
    }
    $stmt->close();
}

// 5. جلب كل الوضعيات المحفوظة لعرضها في الجدول
$saved_positions_result = $conn->query("SELECT id, servo1, servo2, servo3, servo4, created_at FROM angles ORDER BY id DESC");

// 6. إغلاق الاتصال
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🤖 Servo Motors Control Panel</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            direction: ltr;
            position: relative;
            overflow-x: hidden;
        }

        /* خلفية متحركة */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 50%, rgba(120, 119, 198, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 80%, rgba(138, 43, 226, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 20%, rgba(65, 88, 208, 0.3) 0%, transparent 50%);
            animation: gradientShift 15s ease infinite;
            z-index: 0;
        }

        @keyframes gradientShift {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.8; }
        }

        .container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 40px;
            border-radius: 24px;
            box-shadow: 
                0 20px 60px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.1) inset;
            width: 100%;
            max-width: 1000px;
            margin: 0 auto;
            position: relative;
            z-index: 1;
            animation: fadeIn 0.6s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        h2 {
            text-align: center;
            margin-bottom: 30px;
            font-size: 2.2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            font-weight: 700;
            text-shadow: 0 2px 10px rgba(102, 126, 234, 0.1);
        }

        /* رسائل التنبيه */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 500;
            animation: slideDown 0.4s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-success {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(17, 153, 142, 0.3);
        }

        .alert-error {
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(235, 51, 73, 0.3);
        }

        .alert-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(240, 147, 251, 0.3);
        }

        /* شبكة السلايدرات */
        .slider-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 35px;
        }

        .slider-group {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 25px;
            border-radius: 16px;
            border: 2px solid rgba(255, 255, 255, 0.8);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .slider-group::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.1) 0%, rgba(118, 75, 162, 0.1) 100%);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .slider-group:hover::before {
            opacity: 1;
        }

        .slider-group:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .slider-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 1rem;
            color: #2d3748;
            position: relative;
            z-index: 1;
        }

        .slider-group label span {
            float: right;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.9rem;
            box-shadow: 0 2px 8px rgba(102, 126, 234, 0.3);
            transition: all 0.3s ease;
        }

        .slider-group:hover label span {
            transform: scale(1.1);
        }

        /* تصميم السلايدر المخصص */
        input[type="range"] {
            width: 100%;
            -webkit-appearance: none;
            appearance: none;
            height: 8px;
            background: linear-gradient(90deg, #e0e7ff 0%, #667eea 0%);
            border-radius: 10px;
            outline: none;
            transition: all 0.3s ease;
            position: relative;
            z-index: 1;
        }

        input[type="range"]::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 24px;
            height: 24px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            cursor: pointer;
            border: 4px solid #fff;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            transition: all 0.2s ease;
        }

        input[type="range"]::-webkit-slider-thumb:hover {
            transform: scale(1.2);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        input[type="range"]::-webkit-slider-thumb:active {
            transform: scale(1.1);
        }

        input[type="range"]::-moz-range-thumb {
            width: 16px;
            height: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            cursor: pointer;
            border: 4px solid #fff;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
            transition: all 0.2s ease;
        }

        input[type="range"]::-moz-range-thumb:hover {
            transform: scale(1.2);
        }

        /* مجموعة الأزرار */
        .btn-group {
            display: flex;
            gap: 15px;
            margin-bottom: 40px;
            padding-bottom: 40px;
            border-bottom: 2px solid rgba(102, 126, 234, 0.1);
            flex-wrap: wrap;
        }

        .btn {
            flex: 1;
            min-width: 150px;
            padding: 14px 24px;
            border: none;
            border-radius: 12px;
            cursor: pointer;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .btn::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .btn:active::before {
            width: 300px;
            height: 300px;
        }

        .btn-save {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(17, 153, 142, 0.3);
        }

        .btn-save:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(17, 153, 142, 0.4);
        }

        .btn-reset {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(240, 147, 251, 0.3);
        }

        .btn-reset:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(240, 147, 251, 0.4);
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
        }

        .btn:active {
            transform: translateY(-1px);
        }

        /* جدول الوضعيات المحفوظة */
        .saved-positions {
            animation: fadeIn 0.8s ease 0.2s both;
        }

        .saved-positions h3 {
            text-align: center;
            margin-bottom: 20px;
            font-size: 1.6rem;
            color: #2d3748;
        }

        .table-wrapper {
            overflow-x: auto;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .saved-positions table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }

        .saved-positions th,
        .saved-positions td {
            padding: 16px 20px;
            text-align: center;
            border-bottom: 1px solid rgba(102, 126, 234, 0.1);
        }

        .saved-positions th {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-size: 0.85rem;
        }

        .saved-positions tbody tr {
            transition: all 0.3s ease;
        }

        .saved-positions tbody tr:hover {
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.05) 0%, rgba(118, 75, 162, 0.05) 100%);
            transform: scale(1.01);
        }

        .action-btn {
            display: inline-block;
            padding: 8px 16px;
            background: linear-gradient(135deg, #eb3349 0%, #f45c43 100%);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(235, 51, 73, 0.3);
        }

        .action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(235, 51, 73, 0.4);
        }

        /* تأثير البريق */
        @keyframes shine {
            0% { left: -100%; }
            100% { left: 200%; }
        }

        .btn::after {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 50%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }

        .btn:hover::after {
            left: 200%;
        }

        /* استجابة للشاشات الصغيرة */
        @media (max-width: 768px) {
            .container {
                padding: 25px;
            }

            h2 {
                font-size: 1.6rem;
            }

            .slider-grid {
                grid-template-columns: 1fr;
            }

            .btn-group {
                flex-direction: column;
            }

            .btn {
                min-width: 100%;
            }
        }

        /* تأثير النبض للقيم */
        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        .value-pulse {
            animation: pulse 0.3s ease;
        }
    </style>
</head>
<body>

    <div class="container">
        <h2>🤖 Servo Motors Control Panel</h2>
        
        <?php echo $message; ?>

        <form action="index.php" method="POST" id="controlForm">
            
            <div class="slider-grid">
                <div class="slider-group">
                    <label for="servo1">Servo 1 Angle: <span id="servo1_value">90</span>°</label>
                    <input type="range" id="servo1" name="servo1" min="0" max="180" value="90">
                </div>
                
                <div class="slider-group">
                    <label for="servo2">Servo 2 Angle: <span id="servo2_value">90</span>°</label>
                    <input type="range" id="servo2" name="servo2" min="0" max="180" value="90">
                </div>
                
                <div class="slider-group">
                    <label for="servo3">Servo 3 Angle: <span id="servo3_value">90</span>°</label>
                    <input type="range" id="servo3" name="servo3" min="0" max="180" value="90">
                </div>
                
                <div class="slider-group">
                    <label for="servo4">Servo 4 Angle: <span id="servo4_value">90</span>°</label>
                    <input type="range" id="servo4" name="servo4" min="0" max="180" value="90">
                </div>
            </div>

            <div class="btn-group">
                <button type="button" class="btn btn-reset" id="resetBtn">🔄 Reset to 90°</button>
                
                <button type="submit" name="save_position" class="btn btn-save">💾 Save Position</button>
                
                <button type="button" class="btn btn-submit">📡 Submit to ESP</button>
            </div>
            
        </form>

        <div class="saved-positions">
            <h3>📋 Saved Positions</h3>
            <div class="table-wrapper">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Servo 1</th>
                            <th>Servo 2</th>
                            <th>Servo 3</th>
                            <th>Servo 4</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        if ($saved_positions_result->num_rows > 0) {
                            while($row = $saved_positions_result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td><strong>#" . $row["id"] . "</strong></td>";
                                echo "<td>" . $row["servo1"] . "°</td>";
                                echo "<td>" . $row["servo2"] . "°</td>";
                                echo "<td>" . $row["servo3"] . "°</td>";
                                echo "<td>" . $row["servo4"] . "°</td>";
                                echo "<td><a href='index.php?delete=" . $row["id"] . "' class='action-btn' onclick='return confirm(\"🗑️ هل أنت متأكد من حذف هذه الوضعية؟\");'>Delete</a></td>";
                                echo "</tr>";
                            }
                        } else {
                            echo "<tr><td colspan='6' style='text-align:center; padding: 30px; color: #999;'>No saved positions found.</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <script>
        // مصفوفة السلايدرات
        const sliders = [
            { id: 'servo1', valueId: 'servo1_value' },
            { id: 'servo2', valueId: 'servo2_value' },
            { id: 'servo3', valueId: 'servo3_value' },
            { id: 'servo4', valueId: 'servo4_value' }
        ];

        // 1. وظيفة تحديث قيمة السلايدر مع تأثيرات
        sliders.forEach(sliderInfo => {
            const slider = document.getElementById(sliderInfo.id);
            const valueDisplay = document.getElementById(sliderInfo.valueId);
            
            // تحديث الخلفية التدريجية
            function updateSliderBackground(slider) {
                const value = (slider.value - slider.min) / (slider.max - slider.min) * 100;
                slider.style.background = `linear-gradient(90deg, #667eea ${value}%, #e0e7ff ${value}%)`;
            }
            
            // تهيئة الخلفية عند التحميل
            updateSliderBackground(slider);
            
            // تحديث القيمة عند تحريك السلايدر
            slider.oninput = () => {
                valueDisplay.textContent = slider.value;
                updateSliderBackground(slider);
                
                // تأثير النبض
                valueDisplay.classList.remove('value-pulse');
                void valueDisplay.offsetWidth; // إعادة التشغيل
                valueDisplay.classList.add('value-pulse');
            };
        });

        // 2. وظيفة زر "Reset to 90°" مع تأثيرات
        document.getElementById('resetBtn').onclick = () => {
            sliders.forEach((sliderInfo, index) => {
                setTimeout(() => {
                    const slider = document.getElementById(sliderInfo.id);
                    const valueDisplay = document.getElementById(sliderInfo.valueId);
                    
                    slider.value = 90;
                    valueDisplay.textContent = 90;
                    
                    // تحديث الخلفية
                    const value = (90 - slider.min) / (slider.max - slider.min) * 100;
                    slider.style.background = `linear-gradient(90deg, #667eea ${value}%, #e0e7ff ${value}%)`;
                    
                    // تأثير النبض
                    valueDisplay.classList.remove('value-pulse');
                    void valueDisplay.offsetWidth;
                    valueDisplay.classList.add('value-pulse');
                }, index * 100); // تأخير تدريجي لكل سلايدر
            });
        };

        // 3. إخفاء رسائل التنبيه تلقائياً بعد 5 ثواني
        const alerts = document.querySelectorAll('.alert');
        alerts.forEach(alert => {
            setTimeout(() => {
                alert.style.animation = 'slideDown 0.4s ease reverse';
                setTimeout(() => alert.remove(), 400);
            }, 5000);
        });
    </script>

</body>
</html>