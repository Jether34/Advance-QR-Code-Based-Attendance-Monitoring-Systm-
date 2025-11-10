/**
 * Enhanced PDF Generator for Student QR Cards
 * Includes logos, privacy terms, and comprehensive information
 */

class EnhancedStudentCardPDFGenerator {
    constructor() {
        this.pageWidth = 210; // A4 width in mm
        this.pageHeight = 297; // A4 height in mm
        this.margin = 20;
        
        // School logo placeholder (you can replace with actual base64)
        this.schoolLogo = this.generateSchoolLogoSVG();
        this.systemLogo = this.generateSystemLogoSVG();
    }
    
    /**
     * Generate school logo as SVG (placeholder)
     */
    generateSchoolLogoSVG() {
        return 'data:image/svg+xml;base64,' + btoa(`
            <svg width="60" height="60" xmlns="http://www.w3.org/2000/svg">
                <circle cx="30" cy="30" r="28" fill="#218c21" stroke="#fff" stroke-width="2"/>
                <text x="30" y="35" font-family="Arial, sans-serif" font-size="24" font-weight="bold" 
                      text-anchor="middle" fill="white">PNS</text>
                <text x="30" y="50" font-family="Arial, sans-serif" font-size="8" 
                      text-anchor="middle" fill="white">EST. 1950</text>
            </svg>
        `);
    }
    
    /**
     * Generate system logo as SVG (placeholder)
     */
    generateSystemLogoSVG() {
        return 'data:image/svg+xml;base64,' + btoa(`
            <svg width="50" height="50" xmlns="http://www.w3.org/2000/svg">
                <rect width="50" height="50" fill="#007bff" rx="8"/>
                <rect x="10" y="10" width="30" height="30" fill="white" rx="4"/>
                <rect x="15" y="15" width="5" height="5" fill="#007bff"/>
                <rect x="25" y="15" width="5" height="5" fill="#007bff"/>
                <rect x="35" y="15" width="5" height="5" fill="#007bff"/>
                <rect x="15" y="25" width="5" height="5" fill="#007bff"/>
                <rect x="25" y="25" width="5" height="5" fill="#007bff"/>
                <rect x="35" y="25" width="5" height="5" fill="#007bff"/>
                <rect x="15" y="35" width="5" height="5" fill="#007bff"/>
                <rect x="25" y="35" width="5" height="5" fill="#007bff"/>
                <rect x="35" y="35" width="5" height="5" fill="#007bff"/>
            </svg>
        `);
    }
    
    /**
     * Generate comprehensive PDF for student card
     */
    async generatePDF(studentData, qrCanvas) {
        try {
            // Check if jsPDF is available
            if (typeof window.jsPDF === 'undefined') {
                throw new Error('jsPDF library not loaded');
            }

            const { jsPDF } = window;
            const pdf = new jsPDF();
            
            // Add logos and header
            await this.addHeader(pdf);
            
            // Add student information
            this.addStudentInfo(pdf, studentData);
            
            // Add QR code section
            if (qrCanvas) {
                this.addQRCodeSection(pdf, qrCanvas);
            }
            
            // Add purpose and usage information
            this.addPurposeSection(pdf);
            
            // Add privacy terms and conditions
            this.addPrivacyTerms(pdf);
            
            // Add footer
            this.addFooter(pdf);
            
            return pdf;
            
        } catch (error) {
            console.error('Enhanced PDF generation error:', error);
            throw error;
        }
    }
    
    /**
     * Add header with logos and school information
     */
    async addHeader(pdf) {
        // Header background
        pdf.setFillColor(33, 140, 33);
        pdf.rect(0, 0, this.pageWidth, 35, 'F');
        
        // Add school logo
        try {
            pdf.addImage(this.schoolLogo, 'SVG', 15, 5, 20, 20);
        } catch (e) {
            console.log('Logo loading failed, using text fallback');
        }
        
        // Add system logo
        try {
            pdf.addImage(this.systemLogo, 'SVG', this.pageWidth - 35, 5, 20, 20);
        } catch (e) {
            console.log('System logo loading failed');
        }
        
        // School name and title
        pdf.setTextColor(255, 255, 255);
        pdf.setFontSize(20);
        pdf.setFont('helvetica', 'bold');
        pdf.text('PALAWAN NATIONAL SCHOOL', this.pageWidth / 2, 15, { align: 'center' });
        
        pdf.setFontSize(14);
        pdf.text('Digital Student Identification Card', this.pageWidth / 2, 24, { align: 'center' });
        
        pdf.setFontSize(10);
        pdf.setFont('helvetica', 'normal');
        pdf.text('QR Code Attendance Monitoring System', this.pageWidth / 2, 30, { align: 'center' });
        
        // Reset text color
        pdf.setTextColor(0, 0, 0);
    }
    
    /**
     * Add student information section
     */
    addStudentInfo(pdf, studentData) {
        let yPos = 50;
        
        // Student info header
        pdf.setFillColor(240, 248, 255);
        pdf.setDrawColor(33, 140, 33);
        pdf.setLineWidth(1);
        pdf.roundedRect(this.margin, yPos, this.pageWidth - 2 * this.margin, 8, 2, 2, 'FD');
        
        pdf.setFontSize(14);
        pdf.setFont('helvetica', 'bold');
        pdf.setTextColor(33, 140, 33);
        pdf.text('STUDENT INFORMATION', this.pageWidth / 2, yPos + 6, { align: 'center' });
        
        // Student info box
        yPos += 8;
        pdf.setFillColor(255, 255, 255);
        pdf.roundedRect(this.margin, yPos, this.pageWidth - 2 * this.margin, 70, 2, 2, 'FD');
        
        // Reset colors and prepare for student info
        pdf.setTextColor(0, 0, 0);
        pdf.setFontSize(11);
        yPos += 12;
        
        const studentInfo = [
            { label: 'Student ID:', value: studentData.student_id },
            { label: 'Full Name:', value: studentData.full_name },
            { label: 'LRN Number:', value: studentData.lrn || 'N/A' },
            { label: 'Email Address:', value: studentData.email },
            { label: 'Grade Level:', value: studentData.grade_level },
            { label: 'Strand/Track:', value: studentData.strand || 'N/A' },
            { label: 'Section/Block:', value: studentData.section_block || 'N/A' },
            { label: 'Gender:', value: studentData.gender },
            { label: 'Card Generated:', value: new Date().toLocaleDateString('en-PH', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric'
            })}
        ];
        
        studentInfo.forEach((info, index) => {
            const currentY = yPos + (index * 7);
            
            // Label in bold
            pdf.setFont('helvetica', 'bold');
            pdf.text(info.label, this.margin + 5, currentY);
            
            // Value in normal
            pdf.setFont('helvetica', 'normal');
            pdf.text(info.value, this.margin + 45, currentY);
        });
    }
    
    /**
     * Add QR code section
     */
    addQRCodeSection(pdf, qrCanvas) {
        const qrY = 135;
        
        // QR code header
        pdf.setFillColor(240, 248, 255);
        pdf.setDrawColor(33, 140, 33);
        pdf.roundedRect(this.margin, qrY, this.pageWidth - 2 * this.margin, 8, 2, 2, 'FD');
        
        pdf.setFontSize(14);
        pdf.setFont('helvetica', 'bold');
        pdf.setTextColor(33, 140, 33);
        pdf.text('OFFICIAL QR CODE', this.pageWidth / 2, qrY + 6, { align: 'center' });
        
        // QR code with border
        const qrDataUrl = qrCanvas.toDataURL('image/png');
        const qrSize = 60;
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
        
        // QR code validity
        pdf.setTextColor(0, 0, 0);
        pdf.setFontSize(9);
        pdf.setFont('helvetica', 'normal');
        pdf.text('Scan with smartphone camera or QR reader', this.pageWidth / 2, qrCodeY + qrSize + 8, { align: 'center' });
        pdf.text('Contains verified student information', this.pageWidth / 2, qrCodeY + qrSize + 13, { align: 'center' });
    }
    
    /**
     * Add purpose and usage section
     */
    addPurposeSection(pdf) {
        let yPos = 220;
        
        // Purpose header
        pdf.setFillColor(255, 249, 196);
        pdf.setDrawColor(255, 193, 7);
        pdf.roundedRect(this.margin, yPos, this.pageWidth - 2 * this.margin, 8, 2, 2, 'FD');
        
        pdf.setFontSize(12);
        pdf.setFont('helvetica', 'bold');
        pdf.setTextColor(133, 100, 4);
        pdf.text('PURPOSE & USAGE', this.pageWidth / 2, yPos + 6, { align: 'center' });
        
        yPos += 15;
        pdf.setTextColor(0, 0, 0);
        pdf.setFontSize(9);
        pdf.setFont('helvetica', 'normal');
        
        const purposes = [
            '• ATTENDANCE TRACKING: Present this QR code to teachers for daily attendance recording',
            '• IDENTIFICATION: Official student identification within school premises',
            '• ACCESS CONTROL: Entry to school facilities, library, and computer laboratories',
            '• SCHOOL EVENTS: Registration and participation in official school activities',
            '• EMERGENCY CONTACT: Quick access to student information during emergencies'
        ];
        
        purposes.forEach((purpose, index) => {
            pdf.text(purpose, this.margin + 5, yPos + (index * 5));
        });
    }
    
    /**
     * Add privacy terms and conditions
     */
    addPrivacyTerms(pdf) {
        // Add new page for terms and conditions
        pdf.addPage();
        
        let yPos = 30;
        
        // Terms header
        pdf.setFillColor(220, 53, 69);
        pdf.rect(0, 0, this.pageWidth, 25, 'F');
        
        pdf.setTextColor(255, 255, 255);
        pdf.setFontSize(18);
        pdf.setFont('helvetica', 'bold');
        pdf.text('PRIVACY POLICY & TERMS OF USE', this.pageWidth / 2, 15, { align: 'center' });
        
        pdf.setTextColor(0, 0, 0);
        yPos = 40;
        
        // Data Privacy Section
        pdf.setFontSize(14);
        pdf.setFont('helvetica', 'bold');
        pdf.text('DATA PRIVACY AND PROTECTION', this.margin, yPos);
        
        yPos += 10;
        pdf.setFontSize(10);
        pdf.setFont('helvetica', 'normal');
        
        const privacyTerms = [
            'This QR code contains personal information protected under Republic Act 10173 (Data Privacy Act of 2012).',
            'The embedded data includes: Student ID, Name, LRN, Email, Grade Level, Strand, Section, and Gender.',
            'Information is collected solely for educational and administrative purposes of Palawan National School.',
            'Data will not be shared with unauthorized third parties without explicit consent.',
            'Students have the right to access, correct, and request deletion of their personal data.',
            'Any unauthorized use, duplication, or distribution of this QR code is strictly prohibited.',
            ''
        ];
        
        privacyTerms.forEach((term, index) => {
            const lines = pdf.splitTextToSize(term, this.pageWidth - 2 * this.margin);
            lines.forEach((line, lineIndex) => {
                pdf.text(line, this.margin, yPos + (index * 5) + (lineIndex * 5));
            });
        });
        
        yPos += privacyTerms.length * 5 + 10;
        
        // Terms and Conditions Section
        pdf.setFontSize(14);
        pdf.setFont('helvetica', 'bold');
        pdf.text('TERMS AND CONDITIONS', this.margin, yPos);
        
        yPos += 10;
        pdf.setFontSize(10);
        pdf.setFont('helvetica', 'normal');
        
        const termsConditions = [
            '1. AUTHORIZED USE: This QR code is valid only for the named student and official school purposes.',
            '2. RESPONSIBILITY: Students must keep this card secure and report loss or damage immediately.',
            '3. VALIDITY: This card expires at the end of the current academic year and must be renewed.',
            '4. COMPLIANCE: Usage must comply with all school policies and Philippine laws.',
            '5. LIABILITY: The school is not liable for misuse or unauthorized access to this information.',
            '6. UPDATES: Terms may be updated; students will be notified of significant changes.',
            '7. CONTACT: Report issues to the school registrar or IT department immediately.',
            ''
        ];
        
        termsConditions.forEach((term, index) => {
            const lines = pdf.splitTextToSize(term, this.pageWidth - 2 * this.margin);
            lines.forEach((line, lineIndex) => {
                pdf.text(line, this.margin, yPos + (index * 6) + (lineIndex * 4));
            });
        });
        
        yPos += termsConditions.length * 6 + 15;
        
        // Acknowledgment section
        pdf.setFillColor(240, 248, 255);
        pdf.roundedRect(this.margin, yPos, this.pageWidth - 2 * this.margin, 25, 2, 2, 'F');
        
        pdf.setFontSize(12);
        pdf.setFont('helvetica', 'bold');
        pdf.setTextColor(33, 140, 33);
        pdf.text('STUDENT ACKNOWLEDGMENT', this.pageWidth / 2, yPos + 8, { align: 'center' });
        
        pdf.setTextColor(0, 0, 0);
        pdf.setFontSize(9);
        pdf.setFont('helvetica', 'normal');
        pdf.text('By using this QR code, you acknowledge that you have read, understood, and agree', this.pageWidth / 2, yPos + 15, { align: 'center' });
        pdf.text('to comply with all terms, conditions, and privacy policies stated above.', this.pageWidth / 2, yPos + 20, { align: 'center' });
    }
    
    /**
     * Add footer to all pages
     */
    addFooter(pdf) {
        const pageCount = pdf.internal.getNumberOfPages();
        
        for (let i = 1; i <= pageCount; i++) {
            pdf.setPage(i);
            
            // Footer background
            pdf.setFillColor(248, 249, 250);
            pdf.rect(0, this.pageHeight - 20, this.pageWidth, 20, 'F');
            
            pdf.setFontSize(8);
            pdf.setTextColor(100, 100, 100);
            pdf.text(`Generated: ${new Date().toLocaleString('en-PH')}`, this.margin, this.pageHeight - 10);
            pdf.text(`Palawan National School - Digital ID System`, this.pageWidth - this.margin, this.pageHeight - 10, { align: 'right' });
            pdf.text(`Page ${i} of ${pageCount}`, this.pageWidth / 2, this.pageHeight - 5, { align: 'center' });
        }
    }
    
    /**
     * Download the complete PDF
     */
    async downloadPDF(studentData, qrCanvas) {
        try {
            const pdf = await this.generatePDF(studentData, qrCanvas);
            const timestamp = new Date().toISOString().split('T')[0];
            const filename = `${studentData.student_id}-Official-ID-Card-${timestamp}.pdf`;
            
            pdf.save(filename);
            return filename;
        } catch (error) {
            console.error('Enhanced PDF download error:', error);
            throw error;
        }
    }
}

// Maintain backward compatibility
class StudentCardPDFGenerator extends EnhancedStudentCardPDFGenerator {}

// Make available globally
window.EnhancedStudentCardPDFGenerator = EnhancedStudentCardPDFGenerator;
window.StudentCardPDFGenerator = StudentCardPDFGenerator;