# AI Study Assistant - Review Center

## Overview

The Review Center is an AI-powered study tool for students at Palawan National School. It uses **Jether AI** (powered by Llama 3.2) to help students learn from their lessons through intelligent question generation, summarization, and concept explanations.

## Features

### 1. **CSV Lesson Upload**
Students can upload CSV files containing their lesson content. The AI will read and understand all lessons in the file.

**CSV Format:**
```csv
topic,content,difficulty,subject
Photosynthesis,Photosynthesis is the process...,Medium,Biology
Cell Division,Cell division is the process...,Medium,Biology
```

**Required Columns:**
- `topic` - Lesson topic/title
- `content` - Full lesson content/description

**Optional Columns:**
- `difficulty` - Easy, Medium, Hard
- `subject` - Subject name (Biology, Math, etc.)

### 2. **Paste Notes Directly**
Students can also paste lesson notes directly into a text area instead of uploading a CSV.

### 3. **AI Study Tools**

#### **Generate Practice Questions**
Jether creates 10 diverse practice questions:
- 3 Multiple choice questions
- 3 Short answer questions
- 2 Essay questions
- 2 Application/problem-solving questions

All questions are tailored to the student's grade level and strand.

#### **Summarize Lessons**
Creates comprehensive summaries including:
- Main topics covered
- Key concepts and definitions
- Important facts to remember
- Real-world applications

#### **Explain Key Concepts**
Identifies and explains the 5 most important concepts:
- Simple definitions
- Why it's important
- Real-world examples
- Memory tips or mnemonics

#### **Create Flashcards**
Generates 15 flashcards in Front/Back format:
- Key terms and definitions
- Important concepts
- Formulas and facts
- Suitable for quick review

## Technical Implementation

### Files Created/Modified

**Frontend:**
- `review_center.php` - Student interface with CSV upload and AI controls

**Backend:**
- `review_ai.php` - AI processing endpoint using Ollama

**Sample Data:**
- `data/sample_lessons.csv` - Example lesson file for testing

### AI Integration

**Model:** Llama 3.2 (3B parameters)
**Endpoint:** Ollama (http://localhost:11434)
**Processing:** Local, offline, 100% private
**Response Time:** 5-15 seconds depending on task complexity

### How It Works

1. Student uploads CSV or pastes notes
2. JavaScript sends content to `review_ai.php`
3. Backend constructs grade-appropriate prompt
4. Ollama/Llama 3.2 processes the request
5. AI response formatted and displayed to student

### Security

- ✅ Student session validation required
- ✅ Content sanitized before AI processing
- ✅ No data stored permanently
- ✅ Runs completely offline (no cloud API calls)
- ✅ No API keys or costs

## Usage Instructions

### For Students

1. **Access Review Center**
   - Login as student
   - Navigate to Review Center from dashboard

2. **Load Lessons**
   - **Option A:** Upload CSV file with lessons
   - **Option B:** Paste notes directly into text area

3. **Choose AI Task**
   - Click "Generate Practice Questions" for test prep
   - Click "Summarize Lessons" for quick review
   - Click "Explain Key Concepts" for understanding
   - Click "Create Flashcards" for memorization

4. **Study with Results**
   - AI response appears below
   - Copy/paste for later use
   - Try different tasks with same content

### For Developers

**Requirements:**
- Ollama installed with Llama 3.2 model
- PHP 7.4+ with cURL extension
- MySQL database
- Active student session

**Testing:**
1. Use `data/sample_lessons.csv` for testing
2. Monitor `review_ai.php` responses
3. Check Ollama logs: `ollama list`
4. Adjust `num_predict` in `review_ai.php` for longer/shorter responses

**Customization:**
- Modify prompts in `review_ai.php` for different question styles
- Adjust `num_predict` (currently 800) for response length
- Change `temperature` (currently 0.7) for creativity vs consistency
- Add more task types in both files

## Sample CSV File

Download: [sample_lessons.csv](data/sample_lessons.csv)

Includes lessons on:
- Biology (Photosynthesis, Cell Division, Ecosystem)
- Physics (Newton's Laws)
- Chemistry (Chemical Bonding)
- Mathematics (Pythagorean Theorem)
- History (World War 2, Philippine Revolution)
- ICT (Computer Programming)
- Science (Climate Change)

## Benefits

### For Students
- ✅ Personalized study materials
- ✅ Practice questions for any topic
- ✅ Quick summaries before exams
- ✅ Concept explanations in simple terms
- ✅ Flashcards for memorization
- ✅ Grade-appropriate content

### For Teachers
- ✅ Students can self-study effectively
- ✅ Reduces repetitive questions
- ✅ Encourages independent learning
- ✅ Free AI tutoring 24/7

### For School
- ✅ No cost (free AI model)
- ✅ No internet required
- ✅ Student data stays private
- ✅ Exclusive to Palawan National School

## Future Enhancements

- [ ] Save favorite questions/flashcards
- [ ] Quiz mode with scoring
- [ ] Progress tracking
- [ ] Subject-specific prompts
- [ ] Export to PDF
- [ ] Voice reading of summaries
- [ ] Multiple language support
- [ ] Teacher-uploaded lesson libraries

## Creator

**Jether Garque**  
Grade 12 ICT Programming Student  
Palawan National School

Part of the QR-Based Attendance Monitoring System project.

---

*Jether AI Assistant - Making learning smarter, one lesson at a time.*
