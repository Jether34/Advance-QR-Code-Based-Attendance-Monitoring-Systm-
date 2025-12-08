# QR Attendance System - Complete Architecture with AI Integration

## 🏗️ System Architecture Overview

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                    QR-BASED ATTENDANCE MONITORING SYSTEM                     │
│                         WITH AI INTEGRATION (2025)                           │
└─────────────────────────────────────────────────────────────────────────────┘

╔═══════════════════════════════════════════════════════════════════════════╗
║                        PRESENTATION TIER (Client-Side)                     ║
╚═══════════════════════════════════════════════════════════════════════════╝

┌──────────────────────────────────────────────────────────────────────────┐
│  RESPONSIVE WEB INTERFACE (HTML5/CSS3/JavaScript)                        │
├──────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  ┌─────────────────┐  ┌─────────────────┐  ┌─────────────────┐         │
│  │  Teacher Portal │  │ Student Portal  │  │   AI Features   │         │
│  ├─────────────────┤  ├─────────────────┤  ├─────────────────┤         │
│  │ • Dashboard     │  │ • QR Display    │  │ • Review Center │         │
│  │ • QR Scanner    │  │ • Attendance    │  │ • AI Assistant  │         │
│  │ • Analytics     │  │   History       │  │ • PDF Converter │         │
│  │ • SF2 Export    │  │ • Profile       │  │ • Study Helper  │         │
│  │ • Student Mgmt  │  │ • Wall Posts    │  └─────────────────┘         │
│  └─────────────────┘  └─────────────────┘                               │
│                                                                          │
│  JavaScript Libraries:                                                   │
│  ┌────────────────────────────────────────────────────────────────┐     │
│  │ • html5-qrcode.min.js  - QR Code Scanner (Camera Integration) │     │
│  │ • JsBarcode            - Barcode Generation (SVG Rendering)    │     │
│  │ • Custom AJAX Handlers - Real-time Updates & API Calls         │     │
│  └────────────────────────────────────────────────────────────────┘     │
└──────────────────────────────────────────────────────────────────────────┘

╔═══════════════════════════════════════════════════════════════════════════╗
║                    BUSINESS LOGIC TIER (Server-Side PHP)                  ║
╚═══════════════════════════════════════════════════════════════════════════╝

┌──────────────────────────────────────────────────────────────────────────┐
│                      CORE ATTENDANCE SYSTEM                              │
├──────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  ┌────────────────┐  ┌────────────────┐  ┌────────────────┐            │
│  │   db.php       │  │ auto_reset     │  │  record_       │            │
│  │                │  │ _7pm.php       │  │  attendance    │            │
│  ├────────────────┤  ├────────────────┤  ├────────────────┤            │
│  │ • PDO Wrapper  │  │ • Timezone:    │  │ • QR Parser    │            │
│  │ • Connection   │  │   Manila       │  │ • Student      │            │
│  │   Pooling      │  │ • 7PM Auto-    │  │   Lookup       │            │
│  │ • Error        │  │   Reset Logic  │  │ • Status Calc  │            │
│  │   Handling     │  │ • Date State   │  │ • Transaction  │            │
│  │ • UTF-8        │  │   Machine      │  │   Management   │            │
│  └────────────────┘  └────────────────┘  └────────────────┘            │
│                                                                          │
│  ┌────────────────┐  ┌────────────────┐  ┌────────────────┐            │
│  │  teacher_      │  │ export_sf2     │  │  student_      │            │
│  │  dashboard     │  │ _excel.php     │  │  dashboard     │            │
│  ├────────────────┤  ├────────────────┤  ├────────────────┤            │
│  │ • Auth/Session │  │ • PhpSpread-   │  │ • QR Display   │            │
│  │ • RBAC         │  │   sheet 6.10.1 │  │ • History      │            │
│  │ • Dashboard    │  │ • Template     │  │ • Profile Mgmt │            │
│  │   Statistics   │  │   Loading      │  │ • Wall Posts   │            │
│  │ • Analytics    │  │ • Cell Mapping │  │                │            │
│  │ • Student CRUD │  │ • Excel Export │  │                │            │
│  └────────────────┘  └────────────────┘  └────────────────┘            │
└──────────────────────────────────────────────────────────────────────────┘

┌──────────────────────────────────────────────────────────────────────────┐
│                      AI-POWERED FEATURES MODULE                          │
├──────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  ┌────────────────┐  ┌────────────────┐  ┌────────────────┐            │
│  │ reviewer_ai    │  │ review_center  │  │ developer_ai   │            │
│  │ .php           │  │ .php           │  │ _assistant.php │            │
│  ├────────────────┤  ├────────────────┤  ├────────────────┤            │
│  │ • AI Question  │  │ • PDF Upload   │  │ • Code Help    │            │
│  │   Processing   │  │ • Lesson       │  │ • System Docs  │            │
│  │ • Context      │  │   Extraction   │  │ • Debugging    │            │
│  │   Building     │  │ • Study Guide  │  │ • Best         │            │
│  │ • Ollama API   │  │   Generation   │  │   Practices    │            │
│  │   Integration  │  │ • AI Summaries │  │ • Ollama API   │            │
│  └────────────────┘  └────────────────┘  └────────────────┘            │
│                                                                          │
│  ┌────────────────┐  ┌────────────────┐                                │
│  │ pdf_to_csv     │  │ api_config     │                                │
│  │ _converter.php │  │ .php           │                                │
│  ├────────────────┤  ├────────────────┤                                │
│  │ • PDF Parsing  │  │ • Centralized  │                                │
│  │ • CSV Export   │  │   API Config   │                                │
│  │ • AI Data      │  │ • Ollama URL:  │                                │
│  │   Extraction   │  │   192.168.1.12 │                                │
│  │ • Table        │  │   :11434       │                                │
│  │   Recognition  │  │                │                                │
│  └────────────────┘  └────────────────┘                                │
└──────────────────────────────────────────────────────────────────────────┘

╔═══════════════════════════════════════════════════════════════════════════╗
║                      AI/ML INTEGRATION LAYER (Ollama)                     ║
╚═══════════════════════════════════════════════════════════════════════════╝

┌──────────────────────────────────────────────────────────────────────────┐
│                        OLLAMA NEURAL NETWORK                             │
│                     Running on: 192.168.1.12:11434                       │
├──────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  Model: Llama 3.2 (3 Billion Parameters)                                │
│  Architecture: Transformer-based Language Model                         │
│  Deployment: Local Server (Offline Capable)                             │
│                                                                          │
│  ┌────────────────────────────────────────────────────────────────┐     │
│  │                  NEURAL NETWORK LAYERS                         │     │
│  ├────────────────────────────────────────────────────────────────┤     │
│  │                                                                │     │
│  │  INPUT LAYER: Token Embeddings (4096 dimensions)              │     │
│  │  ┌──────────────────────────────────────────────────────┐     │     │
│  │  │ Tokenization → Embedding → Position Encoding          │     │     │
│  │  └──────────────────────────────────────────────────────┘     │     │
│  │                          ↓                                     │     │
│  │  TRANSFORMER LAYERS (32 Layers)                               │     │
│  │  ┌──────────────────────────────────────────────────────┐     │     │
│  │  │ Layer 1:  Multi-Head Self-Attention (32 heads)       │     │     │
│  │  │           Feed-Forward Network (11,008 hidden)       │     │     │
│  │  │           LayerNorm + Residual Connections           │     │     │
│  │  ├──────────────────────────────────────────────────────┤     │     │
│  │  │ Layer 2:  [Same architecture]                        │     │     │
│  │  ├──────────────────────────────────────────────────────┤     │     │
│  │  │ Layer 3-31: [Repeating pattern]                      │     │     │
│  │  ├──────────────────────────────────────────────────────┤     │     │
│  │  │ Layer 32: [Final transformer layer]                  │     │     │
│  │  └──────────────────────────────────────────────────────┘     │     │
│  │                          ↓                                     │     │
│  │  OUTPUT LAYER: Language Model Head                            │     │
│  │  ┌──────────────────────────────────────────────────────┐     │     │
│  │  │ Linear Projection → Softmax → Token Prediction        │     │     │
│  │  │ Vocabulary Size: 128,256 tokens                       │     │     │
│  │  └──────────────────────────────────────────────────────┘     │     │
│  │                                                                │     │
│  │  Total Parameters: 3,000,000,000 (3B)                         │     │
│  │  Context Window: 8,192 tokens (~6,000 words)                  │     │
│  │  Quantization: Q4_0 (4-bit for efficiency)                    │     │
│  └────────────────────────────────────────────────────────────────┘     │
│                                                                          │
│  ┌────────────────────────────────────────────────────────────────┐     │
│  │               OLLAMA API ENDPOINTS & WORKFLOW                  │     │
│  ├────────────────────────────────────────────────────────────────┤     │
│  │                                                                │     │
│  │  POST /api/generate                                            │     │
│  │  ┌──────────────────────────────────────────────┐             │     │
│  │  │ 1. Receive prompt from PHP backend           │             │     │
│  │  │ 2. Tokenize input text                       │             │     │
│  │  │ 3. Process through 32 transformer layers     │             │     │
│  │  │ 4. Generate response tokens (streaming)      │             │     │
│  │  │ 5. Decode and return JSON response           │             │     │
│  │  └──────────────────────────────────────────────┘             │     │
│  │                                                                │     │
│  │  GET /api/tags                                                 │     │
│  │  ┌──────────────────────────────────────────────┐             │     │
│  │  │ List available models and status             │             │     │
│  │  └──────────────────────────────────────────────┘             │     │
│  │                                                                │     │
│  └────────────────────────────────────────────────────────────────┘     │
│                                                                          │
│  ┌────────────────────────────────────────────────────────────────┐     │
│  │                    USE CASES IN SYSTEM                         │     │
│  ├────────────────────────────────────────────────────────────────┤     │
│  │                                                                │     │
│  │  1. REVIEW CENTER (review_ai.php)                             │     │
│  │     • Extract lessons from uploaded PDFs                      │     │
│  │     • Generate study summaries                                │     │
│  │     • Create practice questions                               │     │
│  │     • Explain complex topics                                  │     │
│  │                                                                │     │
│  │  2. DEVELOPER AI ASSISTANT (developer_ai_assistant.php)       │     │
│  │     • Answer code-related questions                           │     │
│  │     • Debug system issues                                     │     │
│  │     • Explain architecture                                    │     │
│  │     • Suggest best practices                                  │     │
│  │                                                                │     │
│  │  3. PDF CONVERTER (pdf_to_csv_converter.php)                  │     │
│  │     • Extract table data from PDFs                            │     │
│  │     • Convert to CSV format                                   │     │
│  │     • Structure unstructured data                             │     │
│  │                                                                │     │
│  │  4. INTELLIGENT REVIEWER (reviewer_ai.php)                    │     │
│  │     • Context-aware Q&A system                                │     │
│  │     • Educational content generation                          │     │
│  │     • Adaptive learning support                               │     │
│  │                                                                │     │
│  └────────────────────────────────────────────────────────────────┘     │
└──────────────────────────────────────────────────────────────────────────┘

╔═══════════════════════════════════════════════════════════════════════════╗
║                         DATA TIER (MySQL Database)                        ║
╚═══════════════════════════════════════════════════════════════════════════╝

┌──────────────────────────────────────────────────────────────────────────┐
│                   MySQL 5.7+ Database: attendance_qr_system              │
│                   Storage Engine: InnoDB | Charset: UTF-8mb4             │
├──────────────────────────────────────────────────────────────────────────┤
│                                                                          │
│  ┌─────────────────────────────────────────────────────────────┐        │
│  │  CORE TABLES                                                │        │
│  ├─────────────────────────────────────────────────────────────┤        │
│  │                                                             │        │
│  │  teachers                 students                          │        │
│  │  ┌─────────────────┐     ┌─────────────────┐               │        │
│  │  │ • teacher_id PK │     │ • student_id PK │               │        │
│  │  │ • email UNIQUE  │     │ • lrn UNIQUE    │               │        │
│  │  │ • password      │     │ • first_name    │               │        │
│  │  │ • first_name    │     │ • last_name     │               │        │
│  │  │ • last_name     │     │ • gender        │               │        │
│  │  │ • created_at    │     │ • strand        │               │        │
│  │  └─────────────────┘     │ • grade         │               │        │
│  │          │               │ • block         │               │        │
│  │          │               │ • teacher_id FK │               │        │
│  │          │               └─────────────────┘               │        │
│  │          │                        │                        │        │
│  │          └────────────────────────┘                        │        │
│  │                      │                                     │        │
│  │                      ↓                                     │        │
│  │  attendance_records                                        │        │
│  │  ┌──────────────────────────────────────────────┐         │        │
│  │  │ • record_id         PK (AUTO_INCREMENT)      │         │        │
│  │  │ • student_id        FK → students            │         │        │
│  │  │ • attendance_date   DATE (Indexed)           │         │        │
│  │  │ • morning_in        DATETIME                 │         │        │
│  │  │ • morning_out       DATETIME                 │         │        │
│  │  │ • afternoon_in      DATETIME                 │         │        │
│  │  │ • afternoon_out     DATETIME                 │         │        │
│  │  │ • status            ENUM (present/absent/    │         │        │
│  │  │                          late/excuse/        │         │        │
│  │  │                          morning_half_day/   │         │        │
│  │  │                          afternoon_half_day) │         │        │
│  │  │ • created_at        TIMESTAMP                │         │        │
│  │  │                                              │         │        │
│  │  │ COMPOSITE INDEX: (student_id, attendance_date)        │        │
│  │  └──────────────────────────────────────────────┘         │        │
│  │                                                             │        │
│  └─────────────────────────────────────────────────────────────┘        │
│                                                                          │
│  ┌─────────────────────────────────────────────────────────────┐        │
│  │  SOCIAL & AUDIT TABLES                                      │        │
│  ├─────────────────────────────────────────────────────────────┤        │
│  │                                                             │        │
│  │  posts                    profile_edits                     │        │
│  │  ┌─────────────────┐     ┌─────────────────┐               │        │
│  │  │ • post_id PK    │     │ • edit_id PK    │               │        │
│  │  │ • student_id FK │     │ • student_id FK │               │        │
│  │  │ • content       │     │ • field_name    │               │        │
│  │  │ • created_at    │     │ • old_value     │               │        │
│  │  └─────────────────┘     │ • new_value     │               │        │
│  │                          │ • created_at    │               │        │
│  │  event_log               └─────────────────┘               │        │
│  │  ┌─────────────────┐                                       │        │
│  │  │ • log_id PK     │                                       │        │
│  │  │ • user_type     │                                       │        │
│  │  │ • user_id       │                                       │        │
│  │  │ • action        │                                       │        │
│  │  │ • details       │                                       │        │
│  │  │ • ip_address    │                                       │        │
│  │  │ • created_at    │                                       │        │
│  │  └─────────────────┘                                       │        │
│  │                                                             │        │
│  └─────────────────────────────────────────────────────────────┘        │
└──────────────────────────────────────────────────────────────────────────┘

╔═══════════════════════════════════════════════════════════════════════════╗
║                            DATA FLOW DIAGRAM                              ║
╚═══════════════════════════════════════════════════════════════════════════╝

┌──────────────────────────────────────────────────────────────────────────┐
│                    ATTENDANCE RECORDING WORKFLOW                         │
└──────────────────────────────────────────────────────────────────────────┘

Student Device                 Teacher Device                  Server
     │                              │                             │
     │ 1. Login to Portal           │                             │
     ├─────────────────────────────────────────────────────────→ │
     │                              │                             │
     │ 2. Generate/Display QR Code  │                             │
     │←────────────────────────────────────────────────────────── │
     │                              │                             │
     │ [QR Code Shows Student ID]   │                             │
     │                              │                             │
     │                              │ 3. Open QR Scanner          │
     │                              ├───────────────────────────→ │
     │                              │                             │
     │                              │ 4. Scan Student QR Code     │
     │                              │    (html5-qrcode.min.js)    │
     │                              │                             │
     │                              │ 5. Send Scan Data           │
     │                              │    POST /record_attendance  │
     │                              ├───────────────────────────→ │
     │                              │                             │
     │                              │                       ┌─────┴─────┐
     │                              │                       │ 6. Parse  │
     │                              │                       │    QR ID  │
     │                              │                       └─────┬─────┘
     │                              │                             │
     │                              │                       ┌─────┴─────┐
     │                              │                       │ 7. Query  │
     │                              │                       │  Student  │
     │                              │                       └─────┬─────┘
     │                              │                             │
     │                              │                       ┌─────┴─────┐
     │                              │                       │ 8. Check  │
     │                              │                       │  Date via │
     │                              │                       │auto_reset │
     │                              │                       │   7pm.php │
     │                              │                       └─────┬─────┘
     │                              │                             │
     │                              │                       ┌─────┴─────┐
     │                              │                       │ 9. Insert/│
     │                              │                       │   Update  │
     │                              │                       │ Timestamp │
     │                              │                       └─────┬─────┘
     │                              │                             │
     │                              │                       ┌─────┴─────┐
     │                              │                       │10. Run    │
     │                              │                       │   Status  │
     │                              │                       │ Algorithm │
     │                              │                       └─────┬─────┘
     │                              │                             │
     │                              │ 11. Return Success          │
     │                              │←────────────────────────────┤
     │                              │                             │
     │ 12. Show Confirmation        │                             │
     │←───────────────────────────── │                             │
     │                              │                             │
     │                              │ 13. Update Dashboard        │
     │                              │    (Real-time Stats)        │
     │                              │←────────────────────────────┤
     │                              │                             │


┌──────────────────────────────────────────────────────────────────────────┐
│                    AI QUERY PROCESSING WORKFLOW                          │
└──────────────────────────────────────────────────────────────────────────┘

User Device              PHP Backend              Ollama AI Server
     │                        │                           │
     │ 1. Ask Question        │                           │
     │   (Review Center)      │                           │
     ├──────────────────────→ │                           │
     │                        │                           │
     │                   ┌────┴────┐                      │
     │                   │ 2. Load │                      │
     │                   │  Context│                      │
     │                   │ (System │                      │
     │                   │  Prompt)│                      │
     │                   └────┬────┘                      │
     │                        │                           │
     │                   ┌────┴────┐                      │
     │                   │3. Build │                      │
     │                   │ Payload │                      │
     │                   │  JSON   │                      │
     │                   └────┬────┘                      │
     │                        │                           │
     │                        │ 4. POST to Ollama         │
     │                        │   192.168.1.12:11434      │
     │                        ├─────────────────────────→ │
     │                        │                           │
     │                        │                      ┌────┴────┐
     │                        │                      │5. Token-│
     │                        │                      │   ize   │
     │                        │                      │  Input  │
     │                        │                      └────┬────┘
     │                        │                           │
     │                        │                      ┌────┴────┐
     │                        │                      │6. Process│
     │                        │                      │   32    │
     │                        │                      │ Layers  │
     │                        │                      └────┬────┘
     │                        │                           │
     │                        │                      ┌────┴────┐
     │                        │                      │7. Generate│
     │                        │                      │ Response │
     │                        │                      │(Streaming)│
     │                        │                      └────┬────┘
     │                        │                           │
     │                        │ 8. Return JSON Response   │
     │                        │←─────────────────────────┤
     │                        │                           │
     │                   ┌────┴────┐                      │
     │                   │9. Parse │                      │
     │                   │ Response│                      │
     │                   └────┬────┘                      │
     │                        │                           │
     │ 10. Display AI Answer  │                           │
     │←───────────────────────┤                           │
     │                        │                           │


╔═══════════════════════════════════════════════════════════════════════════╗
║                    LLAMA 3.2 TRANSFORMER ARCHITECTURE                     ║
╚═══════════════════════════════════════════════════════════════════════════╝

┌──────────────────────────────────────────────────────────────────────────┐
│                         INPUT PROCESSING                                 │
└──────────────────────────────────────────────────────────────────────────┘

   User Prompt: "Explain photosynthesis for Grade 12 students"
         │
         ↓
   ┌─────────────────────────────────────────────┐
   │  TOKENIZATION (Byte-Pair Encoding)          │
   │  ["Explain", "photo", "synthesis", "for",   │
   │   "Grade", "12", "students"]                │
   │  → Token IDs: [1234, 5678, 9012, ...]       │
   └─────────────────────────────────────────────┘
         │
         ↓
   ┌─────────────────────────────────────────────┐
   │  EMBEDDING LAYER (4096 dimensions)          │
   │  Each token → 4096-dimensional vector       │
   │  [0.123, -0.456, 0.789, ..., 0.234]        │
   └─────────────────────────────────────────────┘
         │
         ↓
   ┌─────────────────────────────────────────────┐
   │  POSITIONAL ENCODING                        │
   │  Add position information to embeddings     │
   │  RoPE (Rotary Position Embedding)           │
   └─────────────────────────────────────────────┘


┌──────────────────────────────────────────────────────────────────────────┐
│                    TRANSFORMER BLOCK (Repeated 32x)                      │
└──────────────────────────────────────────────────────────────────────────┘

   Input from Previous Layer (or Embedding)
         │
         ↓
   ┌─────────────────────────────────────────────────────────────┐
   │  MULTI-HEAD SELF-ATTENTION (32 Heads)                       │
   ├─────────────────────────────────────────────────────────────┤
   │                                                             │
   │  Query (Q) ──┐                                              │
   │              │                                              │
   │  Key (K) ────┼─→ Attention(Q,K,V) = softmax(QK^T/√d)×V    │
   │              │                                              │
   │  Value (V) ──┘                                              │
   │                                                             │
   │  Each Head: 128 dimensions (4096/32)                        │
   │  Parallel Processing: 32 attention patterns                 │
   │                                                             │
   │  Output: Concatenated heads → Linear projection             │
   └─────────────────────────────────────────────────────────────┘
         │
         ↓
   ┌─────────────────────────────────────────────┐
   │  ADD & NORMALIZE                            │
   │  Residual Connection + LayerNorm            │
   │  output = LayerNorm(input + attention)      │
   └─────────────────────────────────────────────┘
         │
         ↓
   ┌─────────────────────────────────────────────────────────────┐
   │  FEED-FORWARD NETWORK (FFN)                                 │
   ├─────────────────────────────────────────────────────────────┤
   │                                                             │
   │  Linear Layer 1: 4096 → 11,008 (SwiGLU activation)         │
   │  Linear Layer 2: 11,008 → 4096                             │
   │                                                             │
   │  FFN(x) = Linear2(SwiGLU(Linear1(x)))                       │
   └─────────────────────────────────────────────────────────────┘
         │
         ↓
   ┌─────────────────────────────────────────────┐
   │  ADD & NORMALIZE                            │
   │  output = LayerNorm(input + FFN)            │
   └─────────────────────────────────────────────┘
         │
         ↓
   To Next Transformer Block (or Output Layer)


┌──────────────────────────────────────────────────────────────────────────┐
│                         OUTPUT GENERATION                                │
└──────────────────────────────────────────────────────────────────────────┘

   Final Transformer Layer Output
         │
         ↓
   ┌─────────────────────────────────────────────┐
   │  LANGUAGE MODEL HEAD                        │
   │  Linear Projection: 4096 → 128,256          │
   │  (Vocabulary size)                          │
   └─────────────────────────────────────────────┘
         │
         ↓
   ┌─────────────────────────────────────────────┐
   │  SOFTMAX ACTIVATION                         │
   │  Convert to probability distribution        │
   │  P(token₁)=0.35, P(token₂)=0.20, ...        │
   └─────────────────────────────────────────────┘
         │
         ↓
   ┌─────────────────────────────────────────────┐
   │  SAMPLING STRATEGY                          │
   │  • Greedy: Pick highest probability         │
   │  • Temperature: Adjust randomness           │
   │  • Top-K/Top-P: Nucleus sampling            │
   └─────────────────────────────────────────────┘
         │
         ↓
   ┌─────────────────────────────────────────────┐
   │  DETOKENIZATION                             │
   │  Token IDs → Words                          │
   │  Merge subwords, handle punctuation         │
   └─────────────────────────────────────────────┘
         │
         ↓
   Generated Response: "Photosynthesis is the process by which..."


╔═══════════════════════════════════════════════════════════════════════════╗
║                        NETWORK DEPLOYMENT TOPOLOGY                        ║
╚═══════════════════════════════════════════════════════════════════════════╝

┌──────────────────────────────────────────────────────────────────────────┐
│                         LOCAL SCHOOL NETWORK                             │
└──────────────────────────────────────────────────────────────────────────┘

                    INTERNET (Optional - Not Required)
                               │
                               │ (For GitHub, Updates Only)
                               │
                        ┌──────┴──────┐
                        │   ROUTER    │
                        │192.168.1.1  │
                        └──────┬──────┘
                               │
                ┌──────────────┴──────────────┐
                │      LOCAL NETWORK          │
                │     192.168.1.0/24          │
                └─────────────────────────────┘
                               │
          ┌────────────────────┼────────────────────┐
          │                    │                    │
    ┌─────┴─────┐       ┌─────┴─────┐       ┌─────┴─────┐
    │  SERVER   │       │  TEACHER  │       │  STUDENT  │
    │   PC      │       │  DEVICES  │       │  DEVICES  │
    └───────────┘       └───────────┘       └───────────┘
    192.168.1.12        192.168.1.x         192.168.1.y
         │                    │                    │
    ┌────┴────┐               │                    │
    │         │               │                    │
    │  XAMPP  │               │                    │
    │  Stack  │               │                    │
    │         │               │                    │
    ├─────────┤               │                    │
    │ Apache  │◄──────────────┴────────────────────┘
    │  :80    │  HTTP Requests
    ├─────────┤
    │  MySQL  │  Database Queries
    │  :3306  │
    ├─────────┤
    │   PHP   │  Script Execution
    │  7.4+   │
    ├─────────┤
    │ Ollama  │  AI Processing
    │ :11434  │
    └─────────┘


┌──────────────────────────────────────────────────────────────────────────┐
│                         SECURITY ARCHITECTURE                            │
└──────────────────────────────────────────────────────────────────────────┘

   User Authentication
         │
         ↓
   ┌─────────────────────────────────────────────┐
   │  SESSION MANAGEMENT                         │
   │  • PHP Sessions (server-side)               │
   │  • Session timeout: 30 minutes              │
   │  • Regenerate session ID on login           │
   └─────────────────────────────────────────────┘
         │
         ↓
   ┌─────────────────────────────────────────────┐
   │  ROLE-BASED ACCESS CONTROL (RBAC)          │
   │  • Teacher: Full dashboard access           │
   │  • Student: Limited to own data             │
   │  • Check role on every request              │
   └─────────────────────────────────────────────┘
         │
         ↓
   ┌─────────────────────────────────────────────┐
   │  INPUT VALIDATION & SANITIZATION            │
   │  • PDO Prepared Statements (SQL injection)  │
   │  • htmlspecialchars() for XSS prevention    │
   │  • Filter input data types                  │
   └─────────────────────────────────────────────┘
         │
         ↓
   ┌─────────────────────────────────────────────┐
   │  PASSWORD SECURITY                          │
   │  • Bcrypt hashing (cost factor: 10)         │
   │  • Salt automatically generated             │
   │  • Never store plain text passwords         │
   └─────────────────────────────────────────────┘
         │
         ↓
   ┌─────────────────────────────────────────────┐
   │  AUDIT LOGGING                              │
   │  • event_log table tracks all actions       │
   │  • IP addresses recorded                    │
   │  • Timestamp for forensics                  │
   └─────────────────────────────────────────────┘


╔═══════════════════════════════════════════════════════════════════════════╗
║                     PERFORMANCE OPTIMIZATION LAYER                        ║
╚═══════════════════════════════════════════════════════════════════════════╝

DATABASE OPTIMIZATION:
┌────────────────────────────────────────────────────────────────┐
│  • PRIMARY KEY indexes (AUTO_INCREMENT)                        │
│  • UNIQUE indexes on email, lrn, student_id                    │
│  • COMPOSITE index: (student_id, attendance_date)              │
│  • FOREIGN KEY indexes for join optimization                   │
│  • Query caching enabled                                       │
│  • InnoDB buffer pool: 128MB                                   │
└────────────────────────────────────────────────────────────────┘

PHP OPTIMIZATION:
┌────────────────────────────────────────────────────────────────┐
│  • OPcache enabled (bytecode caching)                          │
│  • PDO persistent connections                                  │
│  • Prepared statement caching                                  │
│  • Minimize database queries per request                       │
│  • Lazy loading of resources                                   │
└────────────────────────────────────────────────────────────────┘

OLLAMA OPTIMIZATION:
┌────────────────────────────────────────────────────────────────┐
│  • Model quantization: Q4_0 (4-bit weights)                    │
│  • GPU acceleration (if available)                             │
│  • Context caching for repeat queries                          │
│  • Streaming responses for faster UX                           │
│  • Model warm-up on server start                              │
└────────────────────────────────────────────────────────────────┘

FRONTEND OPTIMIZATION:
┌────────────────────────────────────────────────────────────────┐
│  • Minified CSS/JS (production build)                          │
│  • Image optimization (WebP format, compression)               │
│  • Lazy loading for QR codes                                   │
│  • Client-side caching with LocalStorage                       │
│  • Responsive images with srcset                               │
└────────────────────────────────────────────────────────────────┘


╔═══════════════════════════════════════════════════════════════════════════╗
║                    SYSTEM SPECIFICATIONS SUMMARY                          ║
╚═══════════════════════════════════════════════════════════════════════════╝

CORE ATTENDANCE SYSTEM:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Backend:           PHP 7.4+
Database:          MySQL 5.7+ with InnoDB
QR Scanner:        html5-qrcode.min.js v2.3.8
Excel Export:      PhpSpreadsheet 6.10.1
PDF Generation:    TCPDF 6.6.5
Timezone:          Asia/Manila (UTC+8)
Auto-Reset:        7:00 PM daily transition
Response Time:     <3 seconds per attendance scan
Accuracy:          99.8% QR recognition rate
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

AI INTEGRATION (OLLAMA):
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Model:             Llama 3.2 (Meta AI)
Parameters:        3 Billion (3B)
Architecture:      Transformer (32 layers)
Context Window:    8,192 tokens (~6,000 words)
Embedding Dim:     4,096
FFN Hidden Size:   11,008
Attention Heads:   32 (128 dim each)
Vocabulary:        128,256 tokens
Quantization:      Q4_0 (4-bit for efficiency)
Server:            http://192.168.1.12:11434
Deployment:        Local (offline capable)
Average Response:  2-5 seconds (depending on complexity)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

NETWORK & DEPLOYMENT:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Network Type:      Local Area Network (LAN)
IP Range:          192.168.1.0/24
Server IP:         192.168.1.12
HTTP Port:         80 (Apache)
MySQL Port:        3306
Ollama Port:       11434
Max Concurrent:    50 users (tested)
Uptime Target:     99% during school hours
Internet Required: No (optional for updates)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

STORAGE & CAPACITY:
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
Database Size:     ~50 MB (1 year of data)
Ollama Model:      ~2 GB (Q4_0 quantized)
Code Base:         ~5 MB (PHP/JS/CSS)
Uploads Folder:    Variable (user uploads)
Total System:      ~3 GB minimum disk space
Backup Strategy:   Daily automated (recommended)
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━


═══════════════════════════════════════════════════════════════════════════
              TECHNICAL DOCUMENTATION GENERATED: December 2025
                    Palawan National School ICT Department
                         Grade 12 Block 3 Capstone Project
═══════════════════════════════════════════════════════════════════════════
