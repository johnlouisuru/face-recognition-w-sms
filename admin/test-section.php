<?php
// session_start();
require_once "db-config/security.php";

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['section_id'])) {
    $_SESSION['section_id'] = $_POST['section_id'];
    $_SESSION['section_name'] = $_POST['section_name'];
    header('Location: monitor.php');
    exit;
}

// Get all sections
$sectionsQuery = "SELECT DISTINCT section_id, section_name FROM students WHERE section_id IS NOT NULL ORDER BY section_name";
$sectionsResult = $conn->query($sectionsQuery);

echo "GIBBERISH!";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Section</title>
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
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 15px;
            padding: 50px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            max-width: 500px;
            width: 100%;
        }
        h1 {
            color: #667eea;
            margin-bottom: 30px;
            text-align: center;
        }
        .form-group {
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin-bottom: 10px;
            font-weight: 600;
            color: #333;
        }
        select {
            width: 100%;
            padding: 15px;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 1.1em;
        }
        select:focus {
            outline: none;
            border-color: #667eea;
        }
        .btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.2em;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
        }
        .btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Select Section to Monitor</h1>
        <form method="POST">
            <div class="form-group">
                <label for="section">Choose a Section:</label>
                <select name="section_id" id="section" onchange="updateSectionName()" required>
                    <option value="">-- Select Section --</option>
                    <?php while ($section = $sectionsResult->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($section['section_id']); ?>" 
                                data-name="<?php echo htmlspecialchars($section['section_name']); ?>">
                            <?php echo htmlspecialchars($section['section_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <input type="hidden" name="section_name" id="section_name">
            </div>
            <button type="submit" class="btn" id="submitBtn" disabled>Start Monitoring</button>
        </form>
    </div>

    <script>
        function updateSectionName() {
            const select = document.getElementById('section');
            const selectedOption = select.options[select.selectedIndex];
            const sectionName = selectedOption.getAttribute('data-name');
            document.getElementById('section_name').value = sectionName;
            document.getElementById('submitBtn').disabled = !select.value;
        }
    </script>
</body>
</html>
<?php $conn->close(); ?>