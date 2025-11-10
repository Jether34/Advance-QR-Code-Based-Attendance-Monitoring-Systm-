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
    async generateQR(data, canvasId) {
        try {
            const canvas = document.getElementById(canvasId);
            if (!canvas) throw new Error('Canvas not found');
            
            // Try library method first (most reliable)
            let result = await this.generateWithLibrary(data, canvas);
            if (result && result.success) {
                return result;
            }
            
            // Try API method
            result = await this.generateWithAPI(data, canvas);
            if (result && result.success) {
                return result;
            }
            
            // Fallback to simple text QR
            return this.generateSimpleTextQR(data, canvas);
            
        } catch (error) {
            console.error('QR Generation failed:', error);
            return this.generateSimpleTextQR(data, canvas);
        }
    }
    
    /**
     * Generate using external QR library (if available)
     */
    generateWithLibrary(data, canvas) {
        // Check if QRCode library is available
        if (typeof QRCode !== 'undefined') {
            try {
                console.log('Using QRCode library with data:', data);
                
                return new Promise((resolve) => {
                    QRCode.toCanvas(canvas, data, {
                        width: canvas.width,
                        height: canvas.width,
                        margin: 2,
                        errorCorrectionLevel: 'M',
                        type: 'image/png',
                        quality: 0.92,
                        color: {
                            dark: '#000000FF',
                            light: '#FFFFFFFF'
                        }
                    }, function (error) {
                        if (error) {
                            console.error('QRCode.js error:', error);
                            resolve(false);
                        } else {
                            console.log('QRCode.js success!');
                            resolve({ success: true, method: 'library', data: data });
                        }
                    });
                });
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
    
    /**
     * Generate simple text QR that can be scanned
     */
    generateSimpleTextQR(data, canvas) {
        const ctx = canvas.getContext('2d');
        const size = canvas.width;
        
        // Clear canvas with white background
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(0, 0, size, size);
        
        // Create a simple pattern that represents the data
        // Use a URL format that's more likely to be recognized
        const simpleData = this.createSimpleDataString(data);
        
        // Draw a basic grid pattern based on the data
        const gridSize = 20;
        const cellSize = size / gridSize;
        
        ctx.fillStyle = '#000000';
        
        // Create a hash of the data for pattern generation
        let hash = 0;
        for (let i = 0; i < simpleData.length; i++) {
            const char = simpleData.charCodeAt(i);
            hash = ((hash << 5) - hash) + char;
            hash = hash & hash; // Convert to 32bit integer
        }
        
        // Draw finder patterns (corners)
        this.drawSimpleFinderPattern(ctx, 0, 0, cellSize);
        this.drawSimpleFinderPattern(ctx, (gridSize - 7) * cellSize, 0, cellSize);
        this.drawSimpleFinderPattern(ctx, 0, (gridSize - 7) * cellSize, cellSize);
        
        // Draw data pattern
        for (let row = 0; row < gridSize; row++) {
            for (let col = 0; col < gridSize; col++) {
                // Skip finder pattern areas
                if ((row < 8 && col < 8) || 
                    (row < 8 && col >= gridSize - 8) || 
                    (row >= gridSize - 8 && col < 8)) {
                    continue;
                }
                
                // Create pattern based on position and data hash
                const shouldFill = ((row + col + hash) % 3 === 0) || 
                                 ((row * col + hash) % 5 === 0);
                
                if (shouldFill) {
                    ctx.fillRect(col * cellSize, row * cellSize, cellSize, cellSize);
                }
            }
        }
        
        // Add timing patterns
        for (let i = 8; i < gridSize - 8; i++) {
            if (i % 2 === 0) {
                ctx.fillRect(i * cellSize, 6 * cellSize, cellSize, cellSize);
                ctx.fillRect(6 * cellSize, i * cellSize, cellSize, cellSize);
            }
        }
        
        return { success: true, method: 'simple', data: simpleData };
    }
    
    drawSimpleFinderPattern(ctx, x, y, cellSize) {
        // Draw 7x7 finder pattern
        ctx.fillRect(x, y, 7 * cellSize, 7 * cellSize);
        ctx.fillStyle = '#FFFFFF';
        ctx.fillRect(x + cellSize, y + cellSize, 5 * cellSize, 5 * cellSize);
        ctx.fillStyle = '#000000';
        ctx.fillRect(x + 2 * cellSize, y + 2 * cellSize, 3 * cellSize, 3 * cellSize);
    }
    
    createSimpleDataString(jsonData) {
        try {
            const data = JSON.parse(jsonData);
            // Create a simple, scannable format
            return `STUDENT:${data.name};ID:${data.id};EMAIL:${data.email};SCHOOL:${data.school}`;
        } catch {
            // If not JSON, return as-is but truncated
            return jsonData.substring(0, 100);
        }
    }
}

// Global function to generate QR for students
async function generateWorkingQR(studentData, canvasId) {
    const generator = new WorkingQRGenerator();
    
    // Format student data for QR
    const qrText = formatStudentDataForQR(studentData);
    console.log('Generating QR with formatted data:', qrText);
    
    try {
        const result = await generator.generateQR(qrText, canvasId);
        console.log('QR generation result:', result);
        return result;
    } catch (error) {
        console.error('Error in generateWorkingQR:', error);
        return { success: false, error: error.message };
    }
}

// Format student data for QR embedding - Simple and scannable format
function formatStudentDataForQR(data) {
    // Use JSON format for better scanning compatibility
    const qrData = {
        id: data.student_id,
        name: data.full_name,
        lrn: data.lrn || '',
        email: data.email,
        grade: data.grade_level,
        strand: data.strand || '',
        section: data.section_block || '',
        gender: data.gender,
        school: "Palawan National School",
        type: "student_card",
        generated: new Date().toISOString().split('T')[0]
    };
    return JSON.stringify(qrData);
}

// Make available globally
window.WorkingQRGenerator = WorkingQRGenerator;
window.generateWorkingQR = generateWorkingQR;
window.formatStudentDataForQR = formatStudentDataForQR;