<?php
require_once "db-config/security.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['section_id'])) {
    $_SESSION['section_id']   = $_POST['section_id'];
    $_SESSION['section_name'] = $_POST['section_name'];
    header('Location: monitor.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Select Section</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">

<style>
body {
    font-family: Segoe UI, sans-serif;
    background: linear-gradient(135deg,#667eea,#764ba2);
    display:flex;
    justify-content:center;
    align-items:center;
    min-height:100vh;
}
.container {
    background:#fff;
    padding:40px;
    border-radius:12px;
    width:100%;
    max-width:420px;
}
h1 { text-align:center; margin-bottom:25px; }
select, button {
    width:100%;
    padding:14px;
    margin-top:10px;
}
button {
    background:#667eea;
    color:#fff;
    border:none;
    border-radius:8px;
    cursor:pointer;
}
button:disabled {
    opacity:.5;
    cursor:not-allowed;
}
</style>
</head>

<body>
<div class="container">
    <h1>📊 Select Section</h1>

    <form method="POST">
        <label>Section</label>
        <select id="section" name="section_id" required>
            <option value="">-- Select Section --</option>
        </select>

        <input type="hidden" name="section_name" id="section_name">

        <button id="submitBtn" disabled>Start Monitoring</button>
    </form>
</div>

<script>
fetch('api/get_sections.php')
    .then(res => res.json())
    .then(res => {
        if (!res.success) return;

        const select = document.getElementById('section');

        res.data.forEach(section => {
            const opt = document.createElement('option');
            opt.value = section.section_id;
            opt.textContent = section.section_name;
            opt.dataset.name = section.section_name;
            select.appendChild(opt);
        });
    });

document.getElementById('section').addEventListener('change', function () {
    const opt = this.options[this.selectedIndex];
    document.getElementById('section_name').value = opt.dataset.name || '';
    document.getElementById('submitBtn').disabled = !this.value;
});
</script>
</body>
</html>
