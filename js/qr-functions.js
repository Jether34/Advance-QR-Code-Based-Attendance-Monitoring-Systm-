// Enhanced offline QR code functionality for card.php
function createFallbackQR(text, size, container) {
    // Use the simple QR generator as fallback
    try {
        var canvas = SimplePatternGenerator.generate(text, size);

        // Clear container and add QR code
        container.innerHTML = '';
        container.appendChild(canvas);

        // Store canvas reference for downloads
        window.currentQRCanvas = canvas;

        // Add fallback message
        var fallbackDiv = document.createElement('div');
        fallbackDiv.style.cssText = 'font-size: 11px; color: #f0ad4e; margin-top: 8px; text-align: center; font-weight: bold;';
        fallbackDiv.innerHTML = '⚡ Simple QR Code (Offline Mode)';
        container.appendChild(fallbackDiv);

        console.log('Fallback QR code generated successfully');
    } catch (error) {
        console.error('All QR generation methods failed:', error);

        // Last resort: show the text directly
        container.innerHTML = `
            <div style="background: #f8f9fa; border: 2px dashed #6c757d; padding: 20px; border-radius: 8px; text-align: center;">
                <strong>QR Code Generation Failed</strong><br>
                <small>Student Data (Manual Entry):</small><br>
                <code style="font-size: 10px; word-break: break-all;">${text.substring(0, 200)}...</code>
            </div>
        `;
    }
}

// Download functions for the QR code
function downloadQRImage() {
    if (window.currentQRCanvas) {
        var link = document.createElement('a');
        link.download = 'student-qr-card-' + studentData.student_id + '.png';
        link.href = window.currentQRCanvas.toDataURL();
        link.click();
    } else {
        alert('QR code is not ready yet. Please wait for it to generate.');
    }
}

function downloadPDF() {
    if (window.currentQRCanvas) {
        // Create a new window for printing
        var printWindow = window.open('', '_blank');
        var qrDataURL = window.currentQRCanvas.toDataURL();

        printWindow.document.write(`
            <html>
            <head>
                <title>Student QR Card - <?php echo htmlspecialchars($user['full_name']); ?></title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; text-align: center; }
                    .card { border: 2px solid #218c21; padding: 20px; border-radius: 10px; max-width: 400px; margin: 0 auto; }
                    .student-info { margin: 15px 0; }
                    .qr-code { margin: 20px 0; }
                    @media print { body { margin: 0; } }
                </style>
            </head>
            <body>
                <div class="card">
                    <h2>🎓 Student QR Card</h2>
                    <div class="student-info">
                        <strong><?php echo htmlspecialchars($user['full_name']); ?></strong><br>
                        Student ID: <?php echo htmlspecialchars($user['student_id']); ?><br>
                        Grade: <?php echo htmlspecialchars($user['grade_level']); ?> - <?php echo htmlspecialchars($user['strand']); ?><br>
                        Section: <?php echo htmlspecialchars($user['section_block']); ?>
                    </div>
                    <div class="qr-code">
                        <img src="${qrDataURL}" alt="Student QR Code" style="max-width: 200px;">
                    </div>
                    <p><small>Scan for attendance tracking</small></p>
                </div>
            </body>
            </html>
        `);

        printWindow.document.close();
        setTimeout(() => {
            printWindow.print();
            printWindow.close();
        }, 500);
    } else {
        alert('QR code is not ready yet. Please wait for it to generate.');
    }
}
