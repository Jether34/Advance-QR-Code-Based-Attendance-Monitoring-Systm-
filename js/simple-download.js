/**
 * Simple and Reliable Download Functions
 * Fallback implementations for PNG and PDF downloads
 */

// Simple PNG download function
function simpleDownloadPNG(canvasId, filename) {
    try {
        console.log('Starting simple PNG download...');

        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            throw new Error('Canvas not found: ' + canvasId);
        }

        console.log('Canvas found, generating data URL...');

        // Method 1: Try direct dataURL download
        try {
            const dataURL = canvas.toDataURL('image/png');
            console.log('Data URL generated, length:', dataURL.length);

            const link = document.createElement('a');
            link.href = dataURL;
            link.download = filename || 'qr-code.png';

            // Force download attribute
            link.setAttribute('download', link.download);

            // Add to body, click, remove
            document.body.appendChild(link);
            console.log('Download link added to DOM');

            link.click();
            console.log('Download triggered');

            // Clean up
            setTimeout(() => {
                try {
                    document.body.removeChild(link);
                    console.log('Download link cleaned up');
                } catch (e) {
                    console.log('Cleanup already done');
                }
            }, 100);

            return true;

        } catch (dataURLError) {
            console.log('DataURL method failed, trying blob method...');

            // Method 2: Try blob conversion
            return new Promise((resolve) => {
                canvas.toBlob(function(blob) {
                    if (blob) {
                        console.log('Blob created, size:', blob.size);

                        const url = URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = filename || 'qr-code.png';

                        document.body.appendChild(link);
                        link.click();
                        document.body.removeChild(link);

                        setTimeout(() => URL.revokeObjectURL(url), 1000);
                        console.log('Blob download completed');
                        resolve(true);
                    } else {
                        console.error('Blob creation failed');
                        resolve(false);
                    }
                }, 'image/png', 1.0);
            });
        }

    } catch (error) {
        console.error('PNG download failed:', error);
        alert('PNG download failed: ' + error.message);
        return false;
    }
}

// Simple PDF download function
function simpleDownloadPDF(studentData, canvasId, filename) {
    try {
        console.log('Starting simple PDF download...');

        // Check jsPDF
        if (typeof window.jsPDF === 'undefined') {
            throw new Error('jsPDF library not available');
        }

        const canvas = document.getElementById(canvasId);
        if (!canvas) {
            throw new Error('Canvas not found: ' + canvasId);
        }

        console.log('Creating PDF...');

        const { jsPDF } = window.jsPDF;
        const pdf = new jsPDF();

        // Add header
        pdf.setFillColor(33, 140, 33);
        pdf.rect(0, 0, 210, 30, 'F');

        pdf.setTextColor(255, 255, 255);
        pdf.setFontSize(18);
        pdf.setFont('helvetica', 'bold');
        pdf.text('PALAWAN NATIONAL SCHOOL', 105, 20, { align: 'center' });

        // Add student info
        pdf.setTextColor(0, 0, 0);
        pdf.setFontSize(14);
        pdf.text('Student QR Code Card', 105, 45, { align: 'center' });

        let y = 60;
        pdf.setFontSize(12);

        if (studentData) {
            const info = [
                ['Student ID:', studentData.student_id],
                ['Name:', studentData.full_name],
                ['LRN:', studentData.lrn || 'N/A'],
                ['Email:', studentData.email],
                ['Grade:', studentData.grade_level]
            ];

            info.forEach(([label, value]) => {
                pdf.setFont('helvetica', 'bold');
                pdf.text(label, 20, y);
                pdf.setFont('helvetica', 'normal');
                pdf.text(value, 60, y);
                y += 8;
            });
        }

        // Add QR code
        try {
            const qrDataURL = canvas.toDataURL('image/png');
            pdf.addImage(qrDataURL, 'PNG', 75, y + 10, 60, 60);
            console.log('QR code added to PDF');
        } catch (qrError) {
            console.log('QR code addition failed:', qrError);
        }

        // Add basic terms
        y += 80;
        pdf.setFontSize(10);
        pdf.text('This QR code is for official school use only.', 20, y);
        pdf.text('Generated: ' + new Date().toLocaleDateString(), 20, y + 5);

        // Download
        const pdfFilename = filename || `${studentData?.student_id || 'student'}-qr-card.pdf`;
        console.log('Saving PDF as:', pdfFilename);

        pdf.save(pdfFilename);
        console.log('PDF save completed');

        return pdfFilename;

    } catch (error) {
        console.error('PDF download failed:', error);
        alert('PDF download failed: ' + error.message);
        return false;
    }
}

// Make functions globally available
window.simpleDownloadPNG = simpleDownloadPNG;
window.simpleDownloadPDF = simpleDownloadPDF;
