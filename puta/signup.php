<?php
// signup.php - renders signup form and handles role-specific fields client-side
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Signup - School Attendance System</title>
    <link rel="stylesheet" href="style.css">
    <style>
        body {
            background: linear-gradient(135deg, #d6f5d6 0%, #eaffea 100%);
            min-height: 100vh;
            padding: 40px 20px;
        }
        .signup-container {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 8px 32px rgba(33, 140, 33, 0.2);
            padding: 40px 32px;
            max-width: 600px;
            margin: 0 auto;
        }
        .signup-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .signup-header h1 {
            color: #218c21;
            font-size: 2em;
            margin-bottom: 8px;
        }
        .signup-header p {
            color: #176617;
            font-size: 0.95em;
        }
        .form-group {
            margin-bottom: 20px;
        }
        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #218c21;
            font-weight: 600;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #b2e2b2;
            border-radius: 8px;
            font-size: 1em;
            transition: all 0.3s;
            box-sizing: border-box;
        }
        .form-group input:focus, .form-group select:focus {
            border-color: #218c21;
            box-shadow: 0 0 0 3px rgba(33, 140, 33, 0.1);
        }
        .row {
            display: flex;
            gap: 15px;
        }
        .col {
            flex: 1;
        }
        .btn-signup {
            width: 100%;
            padding: 14px;
            background: #218c21;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }
        .btn-signup:hover {
            background: #176617;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(33, 140, 33, 0.3);
        }
        .form-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid #b2e2b2;
        }
        .form-footer a {
            color: #218c21;
            font-weight: 600;
            text-decoration: none;
        }
        .form-footer a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="signup-header">
            <h1>Create Account</h1>
            <p>Join School Attendance System</p>
        </div>
        <form id="signupForm" method="post" action="process_signup.php">
            <div class="form-group">
                <label>I am a</label>
                <select name="role" id="role">
                    <option value="student">Student</option>
                    <option value="teacher">Teacher</option>
                </select>
            </div>
            <div id="dynamicFields"></div>
            <button type="submit" class="btn-signup">Create Account</button>
        </form>
        <div class="form-footer">
            <p>Already have an account? <a href="login.php">Login here</a></p>
            <p><a href="index.php">← Back to Home</a></p>
        </div>
    </div>

    <script>
    const roleSel = document.getElementById('role');
    const dynamicFields = document.getElementById('dynamicFields');

    function renderFields(role) {
        let html = '';
        if (role === 'student') {
            html += `
                <div class="form-group" id="lrnField">
                    <label>LRN</label>
                    <input name="lrn" type="text" placeholder="Enter your LRN" required />
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input name="email" type="email" placeholder="Enter your email" required />
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input name="password" type="password" placeholder="Create a password" required minlength="4" />
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input name="full_name" type="text" placeholder="Enter your full name" required />
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" required>
                        <option value="">Select gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Grade</label>
                            <input name="grade" type="text" placeholder="Grade" required />
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Strand <span title='If HUMSS, ABM, or STEM, this field will be auto-filled and disabled.'>(?)</span></label>
                            <input name="strand" id="studentStrand" type="text" placeholder="Strand" required />
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Block/Section (1-20)</label>
                            <input name="block_section" type="number" min="1" max="20" placeholder="Section" required />
                        </div>
                    </div>
                </div>
            `;
        } else if (role === 'teacher') {
            html += `
                <div class="form-group">
                    <label>Faculty</label>
                    <select name="faculty" id="facultySelect" required>
                        <option value="">Select Faculty</option>
                        <option value="ABM">ABM</option>
                        <option value="STEM">STEM</option>
                        <option value="HUMSS">HUMSS</option>
                        <option value="TVL">TVL</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Email Address</label>
                    <input name="email" type="email" placeholder="Enter your email" required />
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input name="password" type="password" placeholder="Create a password" required minlength="4" />
                </div>
                <div class="form-group">
                    <label>Full Name</label>
                    <input name="full_name" type="text" placeholder="Enter your full name" required />
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" required>
                        <option value="">Select gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                <div class="row">
                    <div class="col">
                        <div class="form-group">
                            <label>Grade</label>
                            <input name="grade" type="text" placeholder="Grade" required />
                        </div>
                    </div>
                    <div class="col" id="teacherStrandCol">
                        <div class="form-group">
                            <label>Strand <span title='If HUMSS, ABM, or STEM, this field will be auto-filled and disabled.'>(?)</span></label>
                            <input name="strand" id="teacherStrand" type="text" placeholder="Strand" required />
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Block/Section</label>
                            <input name="block_section" type="number" min="1" max="20" placeholder="Section" required />
                        </div>
                    </div>
                </div>
            `;
        }
        dynamicFields.innerHTML = html;

        // After rendering, apply strand logic for the current role
        if (role === 'student') {
            const studentStrand = document.getElementById('studentStrand');
            if (studentStrand) {
                studentStrand.addEventListener('input', function() {
                    const val = studentStrand.value.trim().toUpperCase();
                    if (["HUMSS","ABM","STEM"].includes(val)) {
                        studentStrand.disabled = true;
                    } else {
                        studentStrand.disabled = false;
                    }
                });
            }
        } else if (role === 'teacher') {
            const facultySel = document.getElementById('facultySelect');
            const teacherStrand = document.getElementById('teacherStrand');
            const teacherStrandCol = document.getElementById('teacherStrandCol');
            if (facultySel && teacherStrand && teacherStrandCol) {
                function updateTeacherStrand() {
                    if (["ABM","STEM","HUMSS"].includes(facultySel.value)) {
                        teacherStrand.value = facultySel.value;
                        teacherStrand.disabled = true;
                        teacherStrandCol.style.display = '';
                    } else if (facultySel.value === "TVL") {
                        teacherStrand.value = '';
                        teacherStrand.disabled = false;
                        teacherStrandCol.style.display = '';
                    } else {
                        teacherStrand.value = '';
                        teacherStrand.disabled = true;
                        teacherStrandCol.style.display = 'none';
                    }
                }
                facultySel.addEventListener('change', updateTeacherStrand);
                updateTeacherStrand();
            }
        }
    }

    roleSel.addEventListener('change', function() {
        renderFields(roleSel.value);
    });
    window.addEventListener('DOMContentLoaded', function() {
        renderFields(roleSel.value);
    });
    </script>