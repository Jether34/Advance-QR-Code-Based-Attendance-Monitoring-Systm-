# Attendance Time Rules & Scanning Conditions

## 📅 **QR Attendance System - Time-Based Scanning Rules**

### ⏰ **Valid Scanning Periods**

The system enforces strict time-based scanning rules to ensure accurate attendance tracking.

---

## 🌅 **Morning Session**

### **Morning In: 6:00 AM - 11:35 AM**

**Time Subdivisions:**

| Time Range | Status | Description |
|------------|--------|-------------|
| **6:00 AM - 9:00 AM** | ✅ **Present** | On-time arrival (Half Day Present if no afternoon) |
| **9:01 AM - 11:35 AM** | ⚠️ **Late** | Late arrival (Marked as Late even with afternoon scan) |

**Rules:**
- Students scanning between 6:00-9:00 AM are marked **on time**
- Students scanning between 9:01-11:35 AM are marked **late**
- If student scans only in morning (no afternoon), status is **morning_half_day**
- If student doesn't scan during this period, they cannot get full day present

### **Morning Out: 11:36 AM - 12:45 PM**

**Purpose:** Track when students leave after morning session

**Rules:**
- Optional scan (not required for attendance status)
- Provides complete tracking of student movement
- Used for security and reporting purposes

---

## 🌆 **Afternoon Session**

### **Afternoon In: 12:46 PM - 3:45 PM**

**Purpose:** Record afternoon session arrival

**Rules:**
- Required for full day attendance
- Students without morning scan but with afternoon scan = **afternoon_half_day**
- Students with both morning + afternoon = **present** (or **late** if morning was after 9:00 AM)

### **Afternoon Out: 3:46 PM - 7:00 PM**

**Purpose:** Track when students leave after afternoon session

**Rules:**
- Optional scan (not required for attendance status)
- Provides complete tracking of student movement
- Last valid scanning period of the day

---

## 📊 **Attendance Status Calculation**

### **Status Determination Logic**

| Morning In | Morning Out | Afternoon In | Afternoon Out | Final Status | Explanation |
|------------|-------------|--------------|---------------|--------------|-------------|
| ✅ Any time | ✅ Yes | ✅ Yes | ✅ Yes | **PRESENT** | All 4 scans completed (perfect attendance) |
| ✅ 6:00-9:00 | ❌ No | ✅ Yes | ❌ No | **PRESENT** | Both IN scans, on time |
| ✅ 9:01-11:35 | ❌ No | ✅ Yes | ❌ No | **LATE** | Both IN scans but arrived late |
| ✅ Any time | ✅/❌ Any | ❌ No | ❌ No | **MORNING_HALF_DAY** | Only morning session |
| ❌ No | ❌ No | ✅ Yes | ✅/❌ Any | **AFTERNOON_HALF_DAY** | Only afternoon session |
| ❌ No | ❌ No | ❌ No | ❌ No | **ABSENT** | No scans recorded |

### **Detailed Status Definitions**

**PRESENT** ✅
- **PERFECT:** All 4 scans completed (morning in + out, afternoon in + out) = PRESENT
- **MINIMUM:** Scanned morning in (6:00-9:00 AM) AND afternoon in (12:46-3:45 PM) = PRESENT
- OUT scans are optional for PRESENT status, but recommended for complete tracking
- Counts as 1.0 attendance day

**LATE** ⚠️
- Scanned morning in (9:01-11:35 AM) AND afternoon in (12:46-3:45 PM)
- Full day but late arrival
- May affect student records depending on school policy

**MORNING_HALF_DAY** 🌅
- Scanned only during morning session (6:00-11:35 AM)
- No afternoon scan
- Counts as 0.5 attendance day

**AFTERNOON_HALF_DAY** 🌆
- Scanned only during afternoon session (12:46-7:00 PM)
- No morning scan
- Counts as 0.5 attendance day

**ABSENT** ❌
- No scans recorded for the entire day
- Automatically marked at 7:00 PM reset
- Counts as 0.0 attendance day

---

## 🚫 **Invalid Scanning Periods**

### **No Scanning Allowed:**

**Before 6:00 AM:**
- ❌ System rejects scans
- Error: "Scanning not allowed at this time"
- Reason: Outside school hours

**Between Periods (Edge Cases):**
- All time slots are covered from 6:00 AM to 7:00 PM
- No gaps in scanning windows

**After 7:00 PM:**
- ❌ System rejects scans
- System automatically resets for next day
- Attendance for current day is finalized

---

## 🔒 **Duplicate Scan Prevention**

### **Rules:**

1. **Once scanned for a period, cannot scan again**
   - Morning In: Can only be scanned once
   - Morning Out: Can only be scanned once
   - Afternoon In: Can only be scanned once
   - Afternoon Out: Can only be scanned once

2. **Error Response:**
   ```json
   {
     "success": false,
     "error": "Already scanned for morning in",
     "message": "You have already scanned for this period",
     "period": "Morning In (On Time)",
     "previous_scan_time": "08:30 AM",
     "current_status": "morning_half_day"
   }
   ```

3. **Students can scan different periods:**
   - ✅ Scan morning in at 7:00 AM
   - ✅ Scan morning out at 12:00 PM
   - ✅ Scan afternoon in at 1:00 PM
   - ✅ Scan afternoon out at 5:00 PM

4. **Students cannot:**
   - ❌ Scan morning in twice (even at different times)
   - ❌ Scan any period multiple times

---

## 📱 **Scanning Process**

### **Step-by-Step:**

1. **Student presents QR code**
2. **System checks current time**
3. **Determines valid period** (morning_in, morning_out, afternoon_in, afternoon_out)
4. **Validates time is within allowed window**
5. **Checks if student already scanned for this period**
6. **Records attendance if valid**
7. **Calculates overall attendance status**
8. **Returns result to scanner**

### **Example Scanning Scenarios:**

**Scenario 1: Perfect Attendance (All 4 Scans)**
- 7:30 AM - Scan morning in ✅ → Status: morning_half_day
- 12:30 PM - Scan morning out ✅ → Status: morning_half_day
- 1:00 PM - Scan afternoon in ✅ → Status: present
- 5:00 PM - Scan afternoon out ✅ → **Status: PRESENT** (Perfect - all 4 scans)

**Scenario 2: Full Day Present (Minimum Required)**
- 8:00 AM - Scan morning in ✅ → Status: morning_half_day
- 1:30 PM - Scan afternoon in ✅ → **Status: PRESENT** (Both IN scans completed)
- (Morning out and afternoon out optional)

**Scenario 3: Late Full Day**
- 10:00 AM - Scan morning in ✅ → Status: morning_half_day (late)
- 2:00 PM - Scan afternoon in ✅ → **Status: LATE**

**Scenario 4: Morning Half Day**
- 8:00 AM - Scan morning in ✅ → Status: morning_half_day
- 12:15 PM - Scan morning out ✅ → **Status: MORNING_HALF_DAY**
- (No afternoon scans)

**Scenario 5: Afternoon Half Day**
- (No morning scans)
- 1:30 PM - Scan afternoon in ✅ → **Status: AFTERNOON_HALF_DAY**
- 6:00 PM - Scan afternoon out ✅ → Status: AFTERNOON_HALF_DAY

**Scenario 6: Absent**
- (No scans all day)
- 7:00 PM - System auto-marks → **Status: ABSENT**

---

## ⚙️ **System Automation**

### **7:00 PM Daily Reset**

**What happens at 7:00 PM:**

1. ✅ All students without any scans → Automatically marked **ABSENT**
2. ✅ Attendance records finalized for the day
3. ✅ System prepares for next day
4. ✅ New scanning period begins for next attendance date

**Auto-Reset Logic:**
- Uses `auto_reset_7pm.php` state machine
- Tracks current attendance date
- After 7:00 PM, scans count toward next day
- Ensures clean daily attendance records

---

## 📈 **Attendance Analytics**

### **Status Calculations:**

**Attendance Rate Formula:**
```
Attendance Rate = (Present Days + (Half Days * 0.5)) / Total School Days * 100%
```

**Example:**
- 20 Present days = 20.0 points
- 5 Late days = 5.0 points (counted as present for calculation)
- 3 Morning Half Days = 1.5 points
- 2 Afternoon Half Days = 1.0 points
- 5 Absent days = 0.0 points
- **Total:** 27.5 / 35 days = **78.57% attendance**

---

## 🔍 **Error Messages**

### **Common Error Responses:**

**Outside Scanning Hours:**
```json
{
  "success": false,
  "error": "Scanning not allowed at this time",
  "message": "Valid scanning hours: 6:00 AM - 7:00 PM",
  "current_time": "05:30 AM",
  "valid_periods": [
    "Morning In: 6:00 AM - 11:35 AM",
    "Morning Out: 11:36 AM - 12:45 PM",
    "Afternoon In: 12:46 PM - 3:45 PM",
    "Afternoon Out: 3:46 PM - 7:00 PM"
  ]
}
```

**Duplicate Scan:**
```json
{
  "success": false,
  "error": "Already scanned for morning in",
  "message": "You have already scanned for this period",
  "period": "Morning In (On Time)",
  "previous_scan_time": "08:30 AM",
  "current_status": "morning_half_day"
}
```

**Student Not Found:**
```json
{
  "success": false,
  "error": "Student not found"
}
```

---

## 📝 **Implementation Notes**

### **Technical Details:**

**Time Validation:**
- All times in Asia/Manila timezone
- Uses 24-hour format internally
- Converts to minutes for comparison
- Precise time boundary checking

**Database Structure:**
- `attendance_records` table stores all scans
- Columns: `morning_in`, `morning_out`, `afternoon_in`, `afternoon_out`
- Each column stores timestamp (DATETIME)
- `status` column stores calculated status
- `attendance_date` tracks which day (respects 7PM reset)

**Status Recalculation:**
- Status recalculated after every scan
- Considers all recorded timestamps
- Checks morning_in time for late determination
- Updates database automatically

---

## 🎯 **Best Practices**

### **For Students:**

✅ **DO:**
- Scan during valid time periods
- Scan both morning and afternoon for full attendance
- Keep QR code ready at scanning time
- Report duplicate scan errors to teacher

❌ **DON'T:**
- Try to scan outside valid hours
- Share QR codes with other students
- Scan multiple times for same period
- Forget to scan both sessions

### **For Teachers:**

✅ **DO:**
- Monitor scanning activity dashboard
- Check for students with incomplete scans
- Review late arrivals (9:01-11:35 AM scans)
- Export attendance reports regularly

❌ **DON'T:**
- Accept manual attendance during scanning hours
- Allow scanning outside system time rules
- Override system-calculated status without reason

---

## 📞 **Support & Questions**

**For technical issues:**
- Check system logs in Developer Dashboard
- Verify time is synced correctly (Asia/Manila)
- Ensure 7PM reset is functioning
- Contact system administrator

**System Files:**
- `record_attendance.php` - Main scanning logic
- `auto_reset_7pm.php` - Daily reset automation
- `ATTENDANCE_TIME_RULES.md` - This documentation

---

**Last Updated:** December 9, 2025  
**System Version:** 2025.12  
**Timezone:** Asia/Manila (UTC+8)
