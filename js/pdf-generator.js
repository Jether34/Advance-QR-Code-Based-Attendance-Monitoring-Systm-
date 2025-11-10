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
            
            // Add title
            pdf.setFontSize(20);
            pdf.setFont('helvetica', 'bold');
            pdf.text('Student QR Code Card', this.pageWidth / 2, 30, { align: 'center' });
            
            // Add school info
            pdf.setFontSize(14);
            pdf.setFont('helvetica', 'normal');
            pdf.text('Palawan National School', this.pageWidth / 2, 45, { align: 'center' });
            pdf.text('Attendance Monitoring System', this.pageWidth / 2, 55, { align: 'center' });
            
            // Add student information box
            let yPos = 80;
            pdf.setDrawColor(0, 100, 0);
            pdf.setLineWidth(0.5);
            pdf.rect(this.margin, yPos, this.pageWidth - 2 * this.margin, 60);
            
            // Student info
            pdf.setFontSize(12);
            pdf.setFont('helvetica', 'bold');
            yPos += 10;
            
            const studentInfo = [
                `Student ID: ${studentData.student_id}`,
                `Full Name: ${studentData.full_name}`,
                `LRN: ${studentData.lrn || 'N/A'}`,
                `Email: ${studentData.email}`,
                `Grade Level: ${studentData.grade_level}`,
                `Strand: ${studentData.strand}`,
                `Section: ${studentData.section_block}`,
                `Gender: ${studentData.gender}`
            ];
            
            studentInfo.forEach((info, index) => {
                pdf.text(info, this.margin + 5, yPos + (index * 7));
            });
            
            // Add QR code
            if (qrCanvas) {
                const qrDataUrl = qrCanvas.toDataURL('image/png');
                const qrSize = 60; // QR size in mm
                const qrX = (this.pageWidth - qrSize) / 2;
                const qrY = 160;
                
                pdf.addImage(qrDataUrl, 'PNG', qrX, qrY, qrSize, qrSize);
                
                // QR code label
                pdf.setFontSize(14);
                pdf.setFont('helvetica', 'bold');
                pdf.text('Scannable QR Code', this.pageWidth / 2, qrY - 10, { align: 'center' });
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