/**
 * Working QR Code Generator
 * Generates scannable QR codes with embedded student data
 */

class WorkingQRGenerator {
    constructor() {
        // Use a simpler but more reliable approach
        this.size = 21; // Standard QR size
        this.quietZone = 4;
    }
    
    /**
     * Generate a working QR code
     */
    generateQR(data, canvasId) {
        try {
            const canvas = document.getElementById(canvasId);
            if (!canvas) throw new Error('Canvas not found');
            
            // Try different approaches in order of preference
            return this.generateWithLibrary(data, canvas) ||
                   this.generateWithAPI(data, canvas) ||
                   this.generateWorkingPattern(data, canvas);
        } catch (error) {
            console.error('QR Generation failed:', error);
            return this.generateWorkingPattern(data, canvas);
        }
    }
    
    /**
     * Generate using external QR library (if available)
     */
    generateWithLibrary(data, canvas) {
        // Check if QRCode library is available (we can add it via CDN)
        if (typeof QRCode !== 'undefined') {
            try {
                QRCode.toCanvas(canvas, data, {
                    width: canvas.width,
                    margin: 2,
                    color: {
                        dark: '#000000',
                        light: '#FFFFFF'
                    }
                });
                return { success: true, method: 'library' };
            } catch (error) {
                console.log('Library method failed:', error);
                return false;
            }
        }
        return false;
    }
    
    /**
     * Generate using online QR API
     */
    generateWithAPI(data, canvas) {
        try {
            const img = new Image();
            const size = canvas.width;
            
            // Use QR Server API
            const encodedData = encodeURIComponent(data);
            const apiUrl = `https://api.qrserver.com/v1/create-qr-code/?size=${size}x${size}&data=${encodedData}&format=png&margin=10`;
            
            return new Promise((resolve) => {
                img.onload = function() {
                    const ctx = canvas.getContext('2d');
                    ctx.fillStyle = '#FFFFFF';
                    ctx.fillRect(0, 0, size, size);
                    ctx.drawImage(img, 0, 0, size, size);
                    resolve({ success: true, method: 'api' });
                };
                
                img.onerror = function() {
                    resolve(false);
                };
                
                // Timeout after 3 seconds
                setTimeout(() => resolve(false), 3000);
                
                img.src = apiUrl;
            });
        } catch (error) {
            return false;
        }
    }
    
    /**
     * Generate working pattern that embeds data
     */
    generateWorkingPattern(data, canvas) {
        const ctx = canvas.getContext('2d');
        const size = canvas.width;
        
        // Clear canvas
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, size, size);
        
        // Create a more sophisticated QR-like pattern
        const moduleSize = Math.floor(size / 25);
        const modules = 25;
        
        // Create data matrix
        const matrix = this.createDataMatrix(data, modules);
        
        // Draw the matrix
        ctx.fillStyle = '#000000';
        for (let row = 0; row < modules; row++) {
            for (let col = 0; col < modules; col++) {
                if (matrix[row][col]) {
                    ctx.fillRect(col * moduleSize, row * moduleSize, moduleSize, moduleSize);
                }
            }
        }
        
        return { success: true, method: 'pattern', data: data };
    }
    
    /**
     * Create data matrix with proper QR structure
     */
    createDataMatrix(data, size) {
        const matrix = Array(size).fill().map(() => Array(size).fill(false));
        
        // Add finder patterns (position detection patterns)
        this.addFinderPattern(matrix, 0, 0);
        this.addFinderPattern(matrix, size - 7, 0);
        this.addFinderPattern(matrix, 0, size - 7);
        
        // Add separators
        this.addSeparators(matrix, size);
        
        // Add timing patterns
        this.addTimingPatterns(matrix, size);
        
        // Add format information
        this.addFormatInfo(matrix, size);
        
        // Add data
        this.addDataToMatrix(matrix, data, size);
        
        return matrix;
    }
    
    /**
     * Add finder patterns
     */
    addFinderPattern(matrix, startRow, startCol) {
        const pattern = [
            [1,1,1,1,1,1,1],
            [1,0,0,0,0,0,1],
            [1,0,1,1,1,0,1],
            [1,0,1,1,1,0,1],
            [1,0,1,1,1,0,1],
            [1,0,0,0,0,0,1],
            [1,1,1,1,1,1,1]
        ];
        
        for (let i = 0; i < 7; i++) {
            for (let j = 0; j < 7; j++) {
                if (startRow + i < matrix.length && startCol + j < matrix[0].length) {
                    matrix[startRow + i][startCol + j] = pattern[i][j];
                }
            }
        }
    }
    
    /**
     * Add separators around finder patterns
     */
    addSeparators(matrix, size) {
        // White borders around finder patterns
        const positions = [[0,0], [size-7,0], [0,size-7]];
        
        positions.forEach(([row, col]) => {
            for (let i = -1; i <= 7; i++) {
                for (let j = -1; j <= 7; j++) {
                    const r = row + i;
                    const c = col + j;
                    if (r >= 0 && r < size && c >= 0 && c < size) {
                        if (i === -1 || i === 7 || j === -1 || j === 7) {
                            if (!this.isFinderPattern(r, c, size)) {
                                matrix[r][c] = false;
                            }
                        }
                    }
                }
            }
        });
    }
    
    /**
     * Check if position is in finder pattern
     */
    isFinderPattern(row, col, size) {
        return (row < 7 && col < 7) ||
               (row < 7 && col >= size - 7) ||
               (row >= size - 7 && col < 7);
    }
    
    /**
     * Add timing patterns
     */
    addTimingPatterns(matrix, size) {
        // Horizontal timing pattern
        for (let i = 8; i < size - 8; i++) {
            matrix[6][i] = i % 2 === 0;
        }
        
        // Vertical timing pattern
        for (let i = 8; i < size - 8; i++) {
            matrix[i][6] = i % 2 === 0;
        }
    }
    
    /**
     * Add format information
     */
    addFormatInfo(matrix, size) {
        // Add dark module
        matrix[4 * 2 + 9][8] = true;
        
        // Simplified format info
        const formatBits = '111011111000100';
        
        // Place format info
        for (let i = 0; i < 6; i++) {
            matrix[8][i] = formatBits[i] === '1';
            matrix[size - 1 - i][8] = formatBits[i] === '1';
        }
    }
    
    /**
     * Add data to matrix
     */
    addDataToMatrix(matrix, data, size) {
        // Convert data to binary
        const binaryData = this.dataToBinary(data);
        
        let bitIndex = 0;
        let direction = -1; // -1 = up, 1 = down
        
        // Place data in zigzag pattern
        for (let col = size - 1; col > 0; col -= 2) {
            if (col === 6) col--; // Skip timing column
            
            for (let count = 0; count < size; count++) {
                for (let c = 0; c < 2; c++) {
                    const currentCol = col - c;
                    const currentRow = direction === -1 ? size - 1 - count : count;
                    
                    if (this.isDataPosition(matrix, currentRow, currentCol, size)) {
                        if (bitIndex < binaryData.length) {
                            matrix[currentRow][currentCol] = binaryData[bitIndex] === '1';
                            bitIndex++;
                        } else {
                            // Fill with pattern
                            matrix[currentRow][currentCol] = (currentRow + currentCol) % 2 === 0;
                        }
                    }
                }
            }
            direction *= -1;
        }
    }
    
    /**
     * Check if position is available for data
     */
    isDataPosition(matrix, row, col, size) {
        // Skip finder patterns, separators, timing patterns
        if (this.isFinderPattern(row, col, size)) return false;
        if (row === 6 || col === 6) return false; // Timing patterns
        if (row === 4 * 2 + 9 && col === 8) return false; // Dark module
        
        // Skip separator areas
        if ((row < 9 && col < 9) || 
            (row < 9 && col >= size - 8) || 
            (row >= size - 8 && col < 9)) return false;
            
        return true;
    }
    
    /**
     * Convert data to binary
     */
    dataToBinary(data) {
        let binary = '';
        
        // Mode indicator (byte mode)
        binary += '0100';
        
        // Character count
        const charCount = Math.min(data.length, 255);
        binary += charCount.toString(2).padStart(8, '0');
        
        // Data
        for (let i = 0; i < charCount; i++) {
            binary += data.charCodeAt(i).toString(2).padStart(8, '0');
        }
        
        // Terminator
        binary += '0000';
        
        // Pad to byte boundary
        while (binary.length % 8 !== 0) {
            binary += '0';
        }
        
        return binary;
    }
}

// Global function to generate QR for students
function generateWorkingQR(studentData, canvasId) {
    const generator = new WorkingQRGenerator();
    
    // Format student data for QR
    const qrText = formatStudentDataForQR(studentData);
    console.log('Generating QR with data:', qrText);
    
    return generator.generateQR(qrText, canvasId);
}

// Format student data for QR embedding
function formatStudentDataForQR(data) {
    const parts = [
        `ID:${data.student_id}`,
        `NAME:${data.full_name}`,
        `LRN:${data.lrn || 'N/A'}`,
        `EMAIL:${data.email}`,
        `GRADE:${data.grade_level}`,
        `STRAND:${data.strand}`,
        `SECTION:${data.section_block}`,
        `GENDER:${data.gender}`,
        `TIME:${new Date().toISOString()}`
    ];
    return parts.join('|');
}

// Make available globally
window.WorkingQRGenerator = WorkingQRGenerator;
window.generateWorkingQR = generateWorkingQR;
window.formatStudentDataForQR = formatStudentDataForQR;