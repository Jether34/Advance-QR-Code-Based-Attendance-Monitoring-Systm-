<?php
// signup.php - renders signup form and handles role-specific fields client-side
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Signup - Palawan National School</title>
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
        .logo-header {
            text-align: center;
            margin-bottom: 24px;
            padding-bottom: 20px;
            border-bottom: 2px solid #b2e2b2;
        }
        .logo-header img {
            max-width: 90px;
            height: auto;
            margin-bottom: 12px;
        }
        .logo-header .school-name {
            color: #196619;
            font-size: 1.2em;
            font-weight: 700;
            margin-bottom: 4px;
        }
        .signup-header {
            text-align: center;
            margin-bottom: 32px;
        }
        .signup-header h1 {
            color: #196619;
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
            color: #196619;
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
            border-color: #196619;
            box-shadow: 0 0 0 3px rgba(25, 102, 25, 0.08);
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
            background: #196619;
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
            background: #155a15;
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
        .terms-checkbox-group {
            margin: 24px 0;
            padding: 16px;
            background: #f0f7f0;
            border-radius: 8px;
            border-left: 4px solid #196619;
        }
        .checkbox-label {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            cursor: pointer;
            color: #176617;
            font-size: 0.95em;
            line-height: 1.4;
        }
        .checkbox-label input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin-top: 2px;
            cursor: pointer;
            accent-color: #196619;
            flex-shrink: 0;
        }
        .terms-links {
            display: flex;
            gap: 16px;
            margin-top: 12px;
            font-size: 0.9em;
            flex-wrap: wrap;
        }
        .terms-links a {
            color: #196619;
            text-decoration: none;
            font-weight: 600;
            border-bottom: 2px solid #b2e2b2;
            padding-bottom: 2px;
        }
        .terms-links a:hover {
            border-bottom-color: #218c21;
        }
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            animation: fadeIn 0.3s;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        .modal-content {
            background-color: #fff;
            margin: 5% auto;
            padding: 32px;
            border-radius: 12px;
            width: 90%;
            max-width: 700px;
            max-height: 80vh;
            overflow-y: auto;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
        }
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 24px;
            padding-bottom: 16px;
            border-bottom: 2px solid #b2e2b2;
        }
        .modal-header h2 {
            color: #196619;
            font-size: 1.6em;
            margin: 0;
        }
        .modal-close {
            font-size: 1.8em;
            font-weight: bold;
            color: #176617;
            cursor: pointer;
            background: none;
            border: none;
            padding: 0;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
        }
        .modal-close:hover {
            background: #f0f7f0;
        }
        .modal-body {
            color: #333;
            font-size: 0.95em;
            line-height: 1.6;
        }
        .modal-body h3 {
            color: #196619;
            margin-top: 20px;
            margin-bottom: 12px;
            font-size: 1.1em;
        }
        .modal-body ul {
            margin-left: 20px;
            margin-bottom: 12px;
        }
        .modal-body li {
            margin-bottom: 8px;
        }
        .modal-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 16px;
            border-top: 1px solid #b2e2b2;
        }
        .btn-modal {
            background: #196619;
            color: #fff;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            font-size: 1em;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
        }
        .btn-modal:hover {
            background: #155a15;
        }
        @media screen and (max-width: 768px) {
            .modal-content {
                width: 95%;
                padding: 24px;
                margin: 20% auto;
            }
            .terms-links {
                gap: 8px;
            }
        }
    </style>
</head>
<body>
    <div class="signup-container">
        <div class="logo-header">
            <picture>
                <source srcset="uploads/System logo.webp" type="image/webp">
                <img src="uploads/System logo.jpg" alt="QR Attendance System Logo" width="70" height="70" loading="eager">
            </picture>
            <div class="school-name">Palawan National School</div>
        </div>
        <div class="signup-header">
            <h1>Create Account</h1>
            <p>Join Hybrid QR Code Based Attendance System</p>
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
            
            <!-- Terms and Conditions Checkbox -->
            <div class="terms-checkbox-group">
                <label class="checkbox-label">
                    <input type="checkbox" name="accept_terms" id="acceptTerms" required />
                    <span>By signing up, I agree to the <a href="#" onclick="openTermsModal(); return false;" style="color: #218c21; font-weight: 600; text-decoration: underline;">Terms and Conditions</a> and <a href="#" onclick="openPrivacyModal(); return false;" style="color: #218c21; font-weight: 600; text-decoration: underline;">Privacy Policy</a></span>
                </label>
            </div>
            
            <button type="submit" class="btn-signup">Create Account</button>
        </form>
        <div class="form-footer">
            <p>Already have an account? <a href="login.php">Login here</a></p>
            <p><a href="index.php">← Back to Home</a></p>
        </div>
    </div>

    <!-- Terms and Conditions Modal -->
    <div id="termsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>📋 Terms and Conditions</h2>
                <button class="modal-close" onclick="closeTermsModal()">&times;</button>
            </div>
            <div class="modal-body">
                <h3>1. Acceptance of Terms</h3>
                <p>By accessing and using the Palawan National School Hybrid QR Code Based Attendance Monitoring System, you accept and agree to be bound by the terms and provision of this agreement.</p>
                
                <h3>2. Use License</h3>
                <p>Permission is granted to temporarily download one copy of the materials (information or software) on the Attendance System for personal, non-commercial transitory viewing only. This is the grant of a license, not a transfer of title, and under this license you may not:</p>
                <ul>
                    <li>Modifying or copying the materials</li>
                    <li>Using the materials for any commercial purpose or for any public display (commercial or non-commercial)</li>
                    <li>Attempting to decompile or reverse engineer any software contained on the system</li>
                    <li>Removing any copyright or other proprietary notations from the materials</li>
                    <li>Transferring the materials to another person or "mirroring" the materials on any other server</li>
                </ul>

                <h3>3. Disclaimer</h3>
                <p>The materials on the Attendance System are provided for educational purposes. Palawan National School does not warrant the accuracy, completeness, or usefulness of this information. Any reliance you place on such material is strictly at your own risk.</p>

                <h3>4. Limitations</h3>
                <p>In no event shall Palawan National School or its suppliers be liable for any damages (including, without limitation, damages for loss of data or profit, or due to business interruption) arising out of the use or inability to use the materials on the Attendance System.</p>

                <h3>5. Accuracy of Materials</h3>
                <p>The materials appearing on the Attendance System could include technical, typographical, or photographic errors. Palawan National School does not warrant that any of the materials on its website are accurate, complete, or current. Palawan National School may make changes to the materials contained on its website at any time without notice.</p>

                <h3>6. Links</h3>
                <p>Palawan National School has not reviewed all of the sites linked to its website and is not responsible for the contents of any such linked site. The inclusion of any link does not imply endorsement by Palawan National School of the site. Use of any such linked website is at the user's own risk.</p>

                <h3>7. Modifications</h3>
                <p>Palawan National School may revise these terms of service for its website at any time without notice. By using this website, you are agreeing to be bound by the then current version of these terms of service.</p>

                <h3>8. Governing Law</h3>
                <p>These terms and conditions are governed by and construed in accordance with the laws of the Republic of the Philippines, and you irrevocably submit to the exclusive jurisdiction of the courts in that location.</p>

                <h3>9. Student Responsibilities</h3>
                <p>Students agree to:</p>
                <ul>
                    <li>Maintain the confidentiality of their login credentials</li>
                    <li>Provide accurate and truthful information during registration</li>
                    <li>Use the system in accordance with school policies</li>
                    <li>Not engage in any unauthorized access or use of the system</li>
                </ul>

                <h3>10. Teacher Responsibilities</h3>
                <p>Teachers agree to:</p>
                <ul>
                    <li>Maintain the confidentiality of student attendance information</li>
                    <li>Use the system only for legitimate educational purposes</li>
                    <li>Follow school policies regarding data management</li>
                    <li>Report any system issues or security concerns immediately</li>
                </ul>
            </div>
            <div class="modal-footer">
                <button class="btn-modal" onclick="closeTermsModal()">I Understand</button>
            </div>
        </div>
    </div>

    <!-- Privacy Policy Modal -->
    <div id="privacyModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>🔒 Privacy Policy</h2>
                <button class="modal-close" onclick="closePrivacyModal()">&times;</button>
            </div>
            <div class="modal-body">
                <h3>1. Information We Collect</h3>
                <p>We collect information you provide directly to us, such as when you create an account or use the Attendance System. This information may include:</p>
                <ul>
                    <li>Name and email address</li>
                    <li>Grade level, strand, and section</li>
                    <li>Attendance records and timestamps</li>
                    <li>Login activity and system usage data</li>
                </ul>

                <h3>2. How We Use Your Information</h3>
                <p>We use the information we collect to:</p>
                <ul>
                    <li>Maintain accurate attendance records</li>
                    <li>Generate reports for teachers and administrators</li>
                    <li>Improve system functionality and user experience</li>
                    <li>Ensure school security and policy compliance</li>
                    <li>Communicate important school-related information</li>
                </ul>

                <h3>3. Data Security</h3>
                <p>We implement appropriate technical and organizational measures to protect your personal information against unauthorized access, alteration, disclosure, or destruction. Your passwords are encrypted using industry-standard security protocols.</p>

                <h3>4. Data Retention</h3>
                <p>Attendance records are retained for the duration of your enrollment at Palawan National School. Upon graduation or withdrawal, records will be maintained in accordance with school policy and applicable legal requirements.</p>

                <h3>5. Access to Your Information</h3>
                <p>You have the right to access, review, and request corrections to your personal information. Please contact your teacher or the administrative office to request access to your records.</p>

                <h3>6. Information Sharing</h3>
                <p>Your personal information is only shared with authorized school personnel who have a legitimate need to access it for educational purposes. We do not sell or share your information with third parties without your consent, except as required by law.</p>

                <h3>7. Student Information Protection</h3>
                <p>The protection of student data is a priority. Access to student information is restricted to authorized school personnel only. Parents/guardians have the right to review their child's attendance records.</p>

                <h3>8. Cookies and Tracking</h3>
                <p>The system uses session cookies to maintain your login session and improve functionality. You can disable cookies in your browser settings, but this may limit system functionality.</p>

                <h3>9. Third-Party Services</h3>
                <p>The Attendance System is hosted locally on school servers. We do not use external cloud services that would transfer your data outside the school network, ensuring maximum data protection and privacy.</p>

                <h3>10. Policy Changes</h3>
                <p>We may update this privacy policy from time to time. Any changes will be posted on this page, and your continued use of the system following the posting of revised Privacy Policy means that you accept and agree to the changes.</p>

                <h3>11. Contact Information</h3>
                <p>If you have questions about this Privacy Policy or our privacy practices, please contact the school administration office. We are committed to addressing your concerns regarding privacy and data protection.</p>

                <h3>12. Compliance</h3>
                <p>This privacy policy complies with the Data Privacy Act of 2012 (Republic Act No. 10173) and other applicable Philippine laws regarding data protection.</p>
            </div>
            <div class="modal-footer">
                <button class="btn-modal" onclick="closePrivacyModal()">I Understand</button>
            </div>
        </div>
    </div>

    <script>
        // Modal functions
        function openTermsModal() {
            document.getElementById('termsModal').style.display = 'block';
        }

        function closeTermsModal() {
            document.getElementById('termsModal').style.display = 'none';
        }

        function openPrivacyModal() {
            document.getElementById('privacyModal').style.display = 'block';
        }

        function closePrivacyModal() {
            document.getElementById('privacyModal').style.display = 'none';
        }

        // Close modal when clicking outside of it
        window.onclick = function(event) {
            var termsModal = document.getElementById('termsModal');
            var privacyModal = document.getElementById('privacyModal');
            
            if (event.target === termsModal) {
                termsModal.style.display = 'none';
            }
            if (event.target === privacyModal) {
                privacyModal.style.display = 'none';
            }
        }

        // Form submission validation
        document.getElementById('signupForm').onsubmit = function(e) {
            var checkbox = document.getElementById('acceptTerms');
            if (!checkbox.checked) {
                e.preventDefault();
                alert('Please accept the Terms and Conditions and Privacy Policy to continue.');
                checkbox.focus();
                return false;
            }
            return true;
        }

        // Signup form field rendering
        const roleSel = document.getElementById('role');
        const dynamicFields = document.getElementById('dynamicFields');

        function renderFields(role) {
            let html = '';
            if (role === 'student') {
                html += `
                    <div class="form-group">
                        <label>Faculty</label>
                        <select name="faculty" id="studentFacultySelect" required>
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
                            <select name="grade" required>
                                <option value="">Select Grade</option>
                                <option value="11">Grade 11</option>
                                <option value="12">Grade 12</option>
                            </select>
                        </div>
                    </div>
                    <div class="col" id="studentStrandCol">
                        <div class="form-group">
                            <label>Strand</label>
                            <input name="strand" id="studentStrand" type="text" placeholder="Strand" required />
                            <select name="strand" id="studentStrandSelect" style="display:none;" required>
                                <option value="">Select TVL Strand</option>
                                <option value="ICT - CSS">ICT - CSS</option>
                                <option value="ICT - PROGRAMMING">ICT - PROGRAMMING</option>
                                <option value="AFA">AFA</option>
                                <option value="ARTS AND DESIGN">ARTS AND DESIGN</option>
                                <option value="HE">HE (HOME ECONOMICS)</option>
                                <option value="IA">IA (INDUSTRIAL ARTS)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Block/Section (1-20)</label>
                            <select name="block_section" required>
                                <option value="">Select Block</option>
                                <option value="1">Block 1</option>
                                <option value="2">Block 2</option>
                                <option value="3">Block 3</option>
                                <option value="4">Block 4</option>
                                <option value="5">Block 5</option>
                                <option value="6">Block 6</option>
                                <option value="7">Block 7</option>
                                <option value="8">Block 8</option>
                                <option value="9">Block 9</option>
                                <option value="10">Block 10</option>
                                <option value="11">Block 11</option>
                                <option value="12">Block 12</option>
                                <option value="13">Block 13</option>
                                <option value="14">Block 14</option>
                                <option value="15">Block 15</option>
                                <option value="16">Block 16</option>
                                <option value="17">Block 17</option>
                                <option value="18">Block 18</option>
                                <option value="19">Block 19</option>
                                <option value="20">Block 20</option>
                            </select>
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
                            <select name="grade" required>
                                <option value="">Select Grade</option>
                                <option value="11">Grade 11</option>
                                <option value="12">Grade 12</option>
                            </select>
                        </div>
                    </div>
                    <div class="col" id="teacherStrandCol">
                        <div class="form-group">
                            <label>Strand</label>
                            <input name="strand" id="teacherStrand" type="text" placeholder="Strand" required />
                            <select name="strand" id="teacherStrandSelect" style="display:none;" required>
                                <option value="">Select TVL Strand</option>
                                <option value="ICT - CSS">ICT - CSS</option>
                                <option value="ICT - PROGRAMMING">ICT - PROGRAMMING</option>
                                <option value="AFA">AFA</option>
                                <option value="ARTS AND DESIGN">ARTS AND DESIGN</option>
                                <option value="HE">HE (HOME ECONOMICS)</option>
                                <option value="IA">IA (INDUSTRIAL ARTS)</option>
                            </select>
                        </div>
                    </div>
                    <div class="col">
                        <div class="form-group">
                            <label>Block/Section (1-20)</label>
                            <select name="block_section" required>
                                <option value="">Select Block</option>
                                <option value="1">Block 1</option>
                                <option value="2">Block 2</option>
                                <option value="3">Block 3</option>
                                <option value="4">Block 4</option>
                                <option value="5">Block 5</option>
                                <option value="6">Block 6</option>
                                <option value="7">Block 7</option>
                                <option value="8">Block 8</option>
                                <option value="9">Block 9</option>
                                <option value="10">Block 10</option>
                                <option value="11">Block 11</option>
                                <option value="12">Block 12</option>
                                <option value="13">Block 13</option>
                                <option value="14">Block 14</option>
                                <option value="15">Block 15</option>
                                <option value="16">Block 16</option>
                                <option value="17">Block 17</option>
                                <option value="18">Block 18</option>
                                <option value="19">Block 19</option>
                                <option value="20">Block 20</option>
                            </select>
                        </div>
                    </div>
                </div>
            `;
        }
        dynamicFields.innerHTML = html;

        // After rendering, apply strand logic for the current role
        if (role === 'student') {
            const studentFacultySel = document.getElementById('studentFacultySelect');
            const studentStrand = document.getElementById('studentStrand');
            const studentStrandSelect = document.getElementById('studentStrandSelect');
            const studentStrandCol = document.getElementById('studentStrandCol');
            if (studentFacultySel && studentStrand && studentStrandSelect && studentStrandCol) {
                function updateStudentStrand() {
                    if (["ABM","STEM","HUMSS"].includes(studentFacultySel.value)) {
                        // For ABM, STEM, HUMSS - auto-fill text input
                        studentStrand.value = studentFacultySel.value;
                        studentStrand.disabled = true;
                        studentStrand.style.display = '';
                        studentStrandSelect.style.display = 'none';
                        studentStrandSelect.removeAttribute('name');
                        studentStrand.setAttribute('name', 'strand');
                        studentStrandCol.style.display = '';
                    } else if (studentFacultySel.value === "TVL") {
                        // For TVL - show dropdown selection
                        studentStrand.style.display = 'none';
                        studentStrand.removeAttribute('name');
                        studentStrandSelect.style.display = '';
                        studentStrandSelect.setAttribute('name', 'strand');
                        studentStrandSelect.required = true;
                        studentStrandCol.style.display = '';
                    } else {
                        // No faculty selected - hide both
                        studentStrand.value = '';
                        studentStrand.style.display = '';
                        studentStrandSelect.style.display = 'none';
                        studentStrandSelect.removeAttribute('name');
                        studentStrand.setAttribute('name', 'strand');
                        studentStrandCol.style.display = 'none';
                    }
                }
                studentFacultySel.addEventListener('change', updateStudentStrand);
                updateStudentStrand();
            }
        } else if (role === 'teacher') {
            const facultySel = document.getElementById('facultySelect');
            const teacherStrand = document.getElementById('teacherStrand');
            const teacherStrandSelect = document.getElementById('teacherStrandSelect');
            const teacherStrandCol = document.getElementById('teacherStrandCol');
            if (facultySel && teacherStrand && teacherStrandSelect && teacherStrandCol) {
                function updateTeacherStrand() {
                    if (["ABM","STEM","HUMSS"].includes(facultySel.value)) {
                        // For ABM, STEM, HUMSS - auto-fill text input
                        teacherStrand.value = facultySel.value;
                        teacherStrand.disabled = true;
                        teacherStrand.style.display = '';
                        teacherStrandSelect.style.display = 'none';
                        teacherStrandSelect.removeAttribute('name');
                        teacherStrand.setAttribute('name', 'strand');
                        teacherStrandCol.style.display = '';
                    } else if (facultySel.value === "TVL") {
                        // For TVL - show dropdown selection
                        teacherStrand.style.display = 'none';
                        teacherStrand.removeAttribute('name');
                        teacherStrandSelect.style.display = '';
                        teacherStrandSelect.setAttribute('name', 'strand');
                        teacherStrandSelect.required = true;
                        teacherStrandCol.style.display = '';
                    } else {
                        // No faculty selected - hide both
                        teacherStrand.value = '';
                        teacherStrand.style.display = '';
                        teacherStrandSelect.style.display = 'none';
                        teacherStrandSelect.removeAttribute('name');
                        teacherStrand.setAttribute('name', 'strand');
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
</body>
</html>