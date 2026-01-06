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
    background: linear-gradient(135deg, #667eea, #764ba2);
    min-height: 100vh;
    display: flex;
    align-items: center;
    justify-content: center;
}
.container {
    background: #fff;
    padding: 45px;
    border-radius: 14px;
    width: 100%;
    max-width: 450px;
}
h1 {
    text-align: center;
    margin-bottom: 25px;
    color: #667eea;
}
label {
    font-weight: 600;
}
select, button{
    width: 100%;
    padding: 14px;
    margin-top: 12px;
    font-size: 1rem;
}
a {
    width: 100%;
    padding: 14px;
    margin-top: 12px;
    font-size: 1rem;
}
button {
    background: #667eea;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}
a {
    background: #667eea;
    color: white;
    border: none;
    border-radius: 8px;
    cursor: pointer;
}
button:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}
</style>
</head>

<body>
<div class="container">
<a href="index.html">Back to Dashboard</a>
<br>
<br>
<hr />

    <h1>📊 Select Section</h1>

    <!-- FORM POSTS TO PHP -->
    <form action="monitor.php" method="POST">
        <label for="section">Choose Section</label>

        <select id="section" name="section_id" required>
            <option value="">-- Select Section --</option>
        </select>

        <input type="hidden" name="section_name" id="section_name">

        <button id="submitBtn" disabled>Start Monitoring</button>
    </form>
</div>

<script>
const sectionSelect = document.getElementById('section');
const sectionNameInput = document.getElementById('section_name');
const submitBtn = document.getElementById('submitBtn');

/* Fetch JSON ONLY */
fetch('api/get_sections.php')
    .then(res => res.json())
    .then(res => {
        if (!res.success) return;

        res.data.forEach(section => {
            const option = document.createElement('option');
            option.value = section.section_id;
            option.textContent = section.section_name;
            option.dataset.name = section.section_name;
            sectionSelect.appendChild(option);
        });
    })
    .catch(err => console.error('Fetch error:', err));

sectionSelect.addEventListener('change', () => {
    const opt = sectionSelect.options[sectionSelect.selectedIndex];
    sectionNameInput.value = opt.dataset.name || '';
    submitBtn.disabled = !sectionSelect.value;
});
</script>
</body>
</html>
