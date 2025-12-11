/**
 * PDF Generator for Student QR Cards
 * Creates downloadable PDF with student info and QR code
 */

class StudentCardPDFGenerator {
    constructor() {
        this.pageWidth = 210; // A4 width in mm
        this.pageHeight = 297; // A4 height in mm
        this.margin = 20;
    }

    /**
     * Generate PDF for student card
     */
    async generatePDF(studentData, qrCanvas) {
        try {
            // Check if jsPDF is available
            if (typeof window.jsPDF === 'undefined') {
                throw new Error('jsPDF library not loaded');
            }

            const { jsPDF } = window;
            const pdf = new jsPDF();

            // Add header with styling
            pdf.setFillColor(33, 140, 33); // Green header
            pdf.rect(0, 0, this.pageWidth, 25, 'F');

            // Add title
            pdf.setTextColor(255, 255, 255); // White text
            pdf.setFontSize(22);
            pdf.setFont('helvetica', 'bold');
            pdf.text('Student QR Code ID Card', this.pageWidth / 2, 15, { align: 'center' });

            // Reset text color
            pdf.setTextColor(0, 0, 0);

            // Add school info
            pdf.setFontSize(16);
            pdf.setFont('helvetica', 'bold');
            pdf.text('Palawan National School', this.pageWidth / 2, 35, { align: 'center' });

            pdf.setFontSize(12);
            pdf.setFont('helvetica', 'normal');
            pdf.text('Digital Attendance Monitoring System', this.pageWidth / 2, 45, { align: 'center' });

            // Add generation date
            pdf.setFontSize(10);
            pdf.setTextColor(100, 100, 100);
            pdf.text(`Generated on: ${new Date().toLocaleDateString('en-US', {
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            })}`, this.pageWidth / 2, 55, { align: 'center' });
            pdf.setTextColor(0, 0, 0);

            // Add student information box with enhanced styling
            let yPos = 70;

            // Student info header
            pdf.setFillColor(240, 248, 255); // Light blue background
            pdf.setDrawColor(33, 140, 33); // Green border
            pdf.setLineWidth(1);
            pdf.roundedRect(this.margin, yPos, this.pageWidth - 2 * this.margin, 8, 2, 2, 'FD');

            pdf.setFontSize(14);
            pdf.setFont('helvetica', 'bold');
            pdf.setTextColor(33, 140, 33);
            pdf.text('STUDENT INFORMATION', this.pageWidth / 2, yPos + 6, { align: 'center' });

            // Student info box
            yPos += 8;
            pdf.setFillColor(255, 255, 255); // White background
            pdf.roundedRect(this.margin, yPos, this.pageWidth - 2 * this.margin, 65, 2, 2, 'FD');

            // Reset colors and prepare for student info
            pdf.setTextColor(0, 0, 0);
            pdf.setFontSize(11);
            pdf.setFont('helvetica', 'normal');
            yPos += 12;

            const studentInfo = [
                { label: 'Student ID:', value: studentData.student_id },
                { label: 'Full Name:', value: studentData.full_name },
                { label: 'LRN Number:', value: studentData.lrn || 'N/A' },
                { label: 'Email Address:', value: studentData.email },
                { label: 'Grade Level:', value: studentData.grade_level },
                { label: 'Strand/Track:', value: studentData.strand || 'N/A' },
                { label: 'Section/Block:', value: studentData.section_block || 'N/A' },
                { label: 'Gender:', value: studentData.gender }
            ];

            studentInfo.forEach((info, index) => {
                const currentY = yPos + (index * 8);

                // Label in bold
                pdf.setFont('helvetica', 'bold');
                pdf.text(info.label, this.margin + 5, currentY);

                // Value in normal
                pdf.setFont('helvetica', 'normal');
                pdf.text(info.value, this.margin + 45, currentY);
            });

            // Add QR code section
            if (qrCanvas) {
                const qrY = 155;

                // QR code header
                pdf.setFillColor(240, 248, 255);
                pdf.setDrawColor(33, 140, 33);
                pdf.roundedRect(this.margin, qrY, this.pageWidth - 2 * this.margin, 8, 2, 2, 'FD');

                pdf.setFontSize(14);
                pdf.setFont('helvetica', 'bold');
                pdf.setTextColor(33, 140, 33);
                pdf.text('SCANNABLE QR CODE', this.pageWidth / 2, qrY + 6, { align: 'center' });

                // QR code with border
                const qrDataUrl = qrCanvas.toDataURL('image/png');
                const qrSize = 65; // QR size in mm
                const qrX = (this.pageWidth - qrSize) / 2;
                const qrCodeY = qrY + 15;

                // White background for QR
                pdf.setFillColor(255, 255, 255);
                pdf.roundedRect(qrX - 3, qrCodeY - 3, qrSize + 6, qrSize + 6, 2, 2, 'F');

                // QR code border
                pdf.setDrawColor(200, 200, 200);
                pdf.setLineWidth(0.5);
                pdf.roundedRect(qrX - 3, qrCodeY - 3, qrSize + 6, qrSize + 6, 2, 2, 'D');

                // Add QR code
                pdf.addImage(qrDataUrl, 'PNG', qrX, qrCodeY, qrSize, qrSize);

                // QR code instructions
                pdf.setTextColor(0, 0, 0);
                pdf.setFontSize(10);
                pdf.setFont('helvetica', 'normal');
                pdf.text('Scan this QR code with any smartphone camera', this.pageWidth / 2, qrCodeY + qrSize + 10, { align: 'center' });
                pdf.text('Contains complete student information for attendance tracking', this.pageWidth / 2, qrCodeY + qrSize + 15, { align: 'center' });
            }

            // Add instructions
            yPos = 240;
            pdf.setFontSize(10);
            pdf.setFont('helvetica', 'normal');
            pdf.text('Instructions:', this.margin, yPos);

            const instructions = [
                '• Present this QR code to your teacher for attendance',
                '• The QR code contains your complete student information',
                '• Keep this card clean and undamaged for best scanning results',
                '• This QR code works offline and contains embedded data',
                '• Report lost or damaged cards to the school office immediately'
            ];

            instructions.forEach((instruction, index) => {
                pdf.text(instruction, this.margin + 5, yPos + 5 + (index * 5));
            });

            // Add footer
            pdf.setFontSize(8);
            pdf.text(`Generated: ${new Date().toLocaleString()}`, this.margin, this.pageHeight - 15);
            pdf.text('Palawan National School QR System', this.pageWidth - this.margin, this.pageHeight - 15, { align: 'right' });

            return pdf;

        } catch (error) {
            console.error('PDF generation error:', error);
            throw error;
        }
    }

    /**
     * Download PDF
     */
    async downloadPDF(studentData, qrCanvas) {
        try {
            const pdf = await this.generatePDF(studentData, qrCanvas);
            const filename = `${studentData.student_id}-qr-card-${new Date().toISOString().split('T')[0]}.pdf`;
            pdf.save(filename);
            return filename;
        } catch (error) {
            console.error('PDF download error:', error);
            throw error;
        }
    }
}

// Make available globally
window.StudentCardPDFGenerator = StudentCardPDFGenerator;
