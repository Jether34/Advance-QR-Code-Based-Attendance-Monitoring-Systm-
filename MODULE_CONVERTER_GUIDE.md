# School Module PDF Converter - Guide

## What It Does

The AI-powered PDF converter now **intelligently extracts ALL lessons** from school modules and converts them to CSV format for use with Jether AI Study Assistant.

## Features

### 🎯 Smart Module Analysis
- **Identifies lesson boundaries** - Recognizes chapters, units, topics
- **Extracts complete content** - Preserves definitions, examples, formulas
- **Multiple lesson detection** - Handles 5-20+ lessons per module
- **Subject classification** - Auto-detects subject (Math, Science, English, etc.)
- **Difficulty assessment** - Analyzes complexity (Easy/Medium/Hard)

### 📚 What Gets Extracted

For each lesson in the module:
- **Topic** - Chapter/lesson title
- **Content** - Key concepts, definitions, examples, formulas (2-5 sentences)
- **Difficulty** - Based on Grade 11-12 standards
- **Subject** - Math, Science, English, Filipino, AP, TLE, ICT, etc.

## How to Use

### For Students:

1. **Get Your Module**
   - Download school module PDF from teacher
   - Or scan physical module to PDF (if text-based)

2. **Upload to Review Center**
   - Go to Review Center → Upload Lessons section
   - Click "PDF File (AI will convert)"
   - Select your module PDF
   - Click "Upload & Convert PDF"

3. **Wait for AI Processing**
   - Takes 30-90 seconds depending on module size
   - AI reads entire module
   - Identifies all lessons automatically
   - Organizes into structured CSV

4. **See Results**
   - Shows number of lessons extracted
   - Example: "Successfully extracted 15 lessons from the module!"
   - Lessons automatically loaded and ready

5. **Use Study Tools**
   - Generate practice questions from all lessons
   - Create summaries
   - Explain concepts
   - Make flashcards

## Example Output

From a typical Grade 11 Math module, AI extracts:

```csv
topic,content,difficulty,subject
"Quadratic Equations","A quadratic equation is ax² + bx + c = 0. Solutions use factoring, completing the square, or quadratic formula x = (-b ± √(b²-4ac))/(2a). Discriminant determines root types.",Medium,Mathematics
"Functions and Relations","A function assigns exactly one output to each input. Notation f(x) represents function value. Types include linear, quadratic, exponential, polynomial.",Medium,Mathematics
"Polynomial Division","Dividing polynomials uses long division or synthetic division. Remainder theorem: f(a) equals remainder when f(x) divided by (x-a). Factor theorem applies.",Hard,Mathematics
```

## Supported Modules

### ✅ Works Great With:
- Math modules (equations, formulas, theorems)
- Science modules (concepts, definitions, laws)
- English modules (grammar, literature, composition)
- Filipino modules (gramatika, panitikan)
- History modules (events, dates, figures)
- ICT modules (programming, concepts, applications)
- TLE modules (procedures, techniques)

### ⚠️ Limitations:
- **PDF must contain readable text** (not scanned images without OCR)
- Complex diagrams/images not preserved (text only)
- Very large modules (100+ pages) may take longer
- Handwritten content not supported

## AI Extraction Process

```
School Module PDF
      ↓
Text Extraction (enhanced PDF parser)
      ↓
Identify Lesson Boundaries
 - Chapter headings
 - Learning objectives
 - Topic separators
 - Numbered sections
      ↓
Extract Each Lesson
 - Topic/Title
 - Full content
 - Key concepts
 - Examples/formulas
      ↓
Classify & Organize
 - Subject identification
 - Difficulty assessment
 - CSV formatting
      ↓
Output Structured CSV
      ↓
Auto-load to Review Center
```

## Technical Details

### Extraction Capabilities:
- **Enhanced PDF parser** - Handles multiple text encoding methods
- **Structure preservation** - Maintains lesson organization
- **Content cleaning** - Removes formatting artifacts
- **Smart chunking** - Identifies logical lesson divisions

### AI Model Settings:
- **Model**: Llama 3.2 (3B parameters)
- **Temperature**: 0.3 (lower = more accurate)
- **Max tokens**: 2000 (handles multiple lessons)
- **Processing time**: 30-90 seconds

### CSV Quality:
- Proper escaping for commas in content
- Preserves important formatting (formulas, definitions)
- Includes comprehensive lesson content (2-5 sentences per lesson)
- Accurate subject and difficulty classification

## Tips for Best Results

### 1. Module Quality
- Use clear, text-based PDFs
- Avoid heavily formatted or image-based modules
- Digital PDFs work better than scanned copies

### 2. File Size
- Ideal: 1-10 MB
- Maximum: 10 MB limit
- Larger modules may need splitting

### 3. Content Type
- Best with structured lessons/chapters
- Works well with numbered sections
- Clear topic headings improve accuracy

### 4. After Conversion
- Review extracted lessons
- Check if all topics captured
- Use CSV download for backup

## Error Messages

**"No lessons could be extracted"**
- PDF may be scanned images
- File may be corrupted
- Try converting to text-based PDF first

**"Connection error. Make sure Ollama is running"**
- Start Ollama service
- Check if Llama 3.2 model installed
- Verify localhost:11434 accessible

**"File too large (max 10MB)"**
- Split module into smaller PDFs
- Compress PDF
- Upload sections separately

## Sample Workflow

**Scenario:** Student has Grade 12 STEM Physics module (20 lessons)

1. Opens Review Center
2. Uploads "Physics_Module_Quarter2.pdf"
3. Waits 60 seconds
4. AI extracts 20 lessons:
   - Newton's Laws
   - Work and Energy
   - Momentum
   - Rotational Motion
   - etc.
5. Clicks "Generate Practice Questions"
6. Gets 10 questions covering all 20 lessons
7. Studies with AI-generated materials

## Future Enhancements

- [ ] OCR support for scanned modules
- [ ] Image/diagram extraction
- [ ] Multi-language support (Filipino, Spanish)
- [ ] Batch PDF upload
- [ ] Module library (save favorites)
- [ ] Share converted modules with classmates
- [ ] Export to Anki flashcards
- [ ] Module progress tracking

## Creator

**Jether Garque**
Grade 12 ICT Programming Student
Palawan National School

Making study smarter with AI! 📚🤖
