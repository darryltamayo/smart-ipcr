# Admin Guide: Pag-open ng Period (Fixed)

## ✅ Ano ang Na-fix?

Dati, pagkatapos mag-open ng period:
- ❌ Walang nakikitang forms sa dashboard
- ❌ Empty ang review queue
- ❌ Faculty ay confused kung paano magsimula
- ❌ Submission tracking ay mali

**Ngayon (Fixed):**
- ✅ Automatic na gawa ng IPCR forms para sa lahat ng faculty
- ✅ Dashboard at review queue ay populated agad
- ✅ Faculty makikita na ang kanilang form ready na
- ✅ Accurate ang submission tracking from day 1

## 📋 Step-by-Step: Paano Mag-open ng Period

### 1. Siguraduhing May Template

Pumunta sa **Admin → Templates**
- Make sure may existing IPCR template
- I-note ang template name

### 2. Gumawa ng Bagong Period

Pumunta sa **Admin → Rating Periods**

Pindutin ang **"New Period"** button:

```
Period Name: January - June 2026 (1st Semester)
Academic Year: 2025-2026 (optional)
Submission Deadline: 2026-06-15
Review Deadline: 2026-06-25
IPCR Template: [Select your template]
```

**💡 Tip:** Gamitin ang Quick Fill buttons:
- **1st Sem** - Auto-fill for Jan-June period
- **2nd Sem** - Auto-fill for July-Dec period
- **Summer** - Auto-fill for summer period

I-click ang **"Save Period"**

### 3. I-open ang Period

Sa periods table, hanapin ang period na ginawa mo.

Status nito ay **"Draft"** (gray badge)

I-click ang **"Open"** button (green button)

Makikita mo ang confirmation:
```
"Open this period? Faculty can submit IPCRs and deans can rate them."
```

I-click ang **"OK"**

### 4. ✨ Automatic na Nangyayari

Pagkatapos mag-confirm, ang system ay:

1. **Binabago ang status to "Open"** (green badge)
2. **Auto-create ng IPCR forms** para sa lahat ng eligible faculty
3. **Nagpapadala ng email notifications** sa lahat ng faculty
4. **Naglo-log sa audit trail**

Makikita mo ang success message:
```
"Period status updated to: open | X IPCR form(s) created for faculty."
```

### 5. I-verify ang Results

#### Option A: Test Page (Recommended)

Pumunta sa: `http://localhost/ipcr-system/test-period-opening.php`

Makikita mo:
- ✅ **Green** - All faculty with forms (PASS)
- ❌ **Red** - Faculty without forms (FAIL)

Detailed table showing:
- Employee ID
- Faculty name
- Has Dean? (✓ or ✗)
- IPCR Form Status
- Form ID
- Created timestamp

#### Option B: Dashboard Check

Pumunta sa **Dashboard**

Check ang metrics:
- **"X Faculty with Forms"** - dapat may numbers na
- **Submission Tracking** - dapat nakikita na ang:
  - 0 On-Time Submissions (expected - walang submitted pa)
  - 0 Late Submissions (expected)
  - X Not Submitted (count ng active faculty)

#### Option C: Review Queue (Para sa Dean)

Kung ikaw ay dean, pumunta sa **Dean → Review Queue**

Filter by the period you opened

Makikita mo ang lahat ng faculty with their draft forms

## 🎯 Sino ang Makakakuha ng Auto-created Forms?

Faculty ay makakakuha ng form KUNG:
- ✅ `role = 'faculty'`
- ✅ `is_active = 1` (Active account)
- ✅ `assigned_dean_id IS NOT NULL` (May assigned dean)
- ✅ Period has `template_id` assigned

Faculty ay HINDI makakakuha ng form KUNG:
- ❌ Inactive account (`is_active = 0`)
- ❌ Walang assigned dean (`assigned_dean_id = NULL`)
- ❌ Role ay hindi 'faculty'

## 📧 Email Notifications

Lahat ng eligible faculty ay makakareceive ng email:

**Subject:** ISU IPCR System - New Evaluation Period is Now Open

**Content:**
```
Hello [Faculty Name],

A new IPCR evaluation period has been opened:

PERIOD: January - June 2026 (1st Semester)
SUBMISSION DEADLINE: June 15, 2026

✅ YOUR IPCR FORM HAS BEEN AUTOMATICALLY CREATED

ACTION REQUIRED:
1. Login to the IPCR System
2. Go to 'My IPCR Form' to fill out your evaluation
3. Upload required evidence documents
4. Submit before the deadline
```

## 🔧 Troubleshooting

### Problem: Faculty walang form after opening period

**Possible Causes:**
1. **Walang assigned dean** - Check: Admin → Users → Edit faculty → Assigned Dean
2. **Inactive account** - Check: Admin → Users → Status should be "Active"
3. **Period walang template** - Check: Admin → Periods → Template column

**Solution:**
1. Fix ang issue (assign dean, activate account, etc.)
2. Close at i-reopen ang period, OR
3. Faculty can visit their IPCR Form page (fallback will create)

### Problem: Specific faculty ay nag-fail sa auto-creation

**Check the audit log:**
Pumunta sa **Admin → Audit Log**

Filter by:
- Action: `created`
- Target Type: `ipcr_form`
- Date: Period opening date

Hanapin kung may error messages

### Problem: Email notifications hindi nakarating

**Possible Causes:**
1. Email configuration ay mali - Check `config/email.php`
2. Personal email ay walang laman - Check faculty profile
3. Mail server ay down

**Note:** Email failures ay HINDI hihinto sa form creation. Forms ay created pa rin kahit mag-fail ang email.

## 📊 What Happens to the Data?

### Database Changes

Pag nag-open ng period, ang system ay:

**1. Updates `rating_periods` table:**
```sql
UPDATE rating_periods 
SET status='open', updated_at=NOW() 
WHERE id=[period_id]
```

**2. Creates records sa `ipcr_forms` table:**
```sql
INSERT INTO ipcr_forms 
(period_id, user_id, template_id, status, created_at) 
VALUES ([pid], [faculty_id], [template_id], 'draft', NOW())
```
- One record per eligible faculty
- Status = 'draft'
- Form is empty (no entries yet)

**3. Logs sa `form_history` table:**
```sql
INSERT INTO form_history 
(form_id, action, done_by, remarks) 
VALUES ([form_id], 'created', [admin_id], 'IPCR form auto-created when period opened.')
```

**4. Logs sa `audit_log` table:**
- Period status change: `status_change` → `open`
- Each form creation: `created` → `ipcr_form`

## 🔄 Late-Added Faculty

Kung may faculty na na-add AFTER opening ng period:

**Automatic Fallback:**
1. Faculty logs in
2. Pumunta sa "My IPCR Form"
3. System detects walang form
4. Auto-create ng form (fallback mechanism)
5. Audit log remark: `"IPCR form created (late enrollment)"`

No manual intervention needed!

## 🎓 Best Practices

### Before Opening Period

✅ **Verify template** - Test ang IPCR template kung complete
✅ **Assign deans** - All faculty should have assigned deans
✅ **Activate accounts** - Make sure all faculty accounts are active
✅ **Set deadlines** - Clear submission and review deadlines
✅ **Test email** - Send test email to verify configuration

### After Opening Period

✅ **Run test page** - Verify all forms were created
✅ **Check audit log** - Review any errors or issues
✅ **Notify faculty** - Remind them via other channels (e.g., group chat)
✅ **Monitor dashboard** - Watch submission progress

### During Period

✅ **Check submission tracking** - Monitor who hasn't submitted
✅ **Send reminders** - Use "Notify Not Submitted" feature
✅ **Grant extensions** - For special cases
✅ **Review submissions** - Deans should review regularly

## 📞 Support

Kung may problema pa rin after following this guide:

1. **Check test page results** - `test-period-opening.php`
2. **Review audit log** - Look for error messages
3. **Check error logs** - XAMPP → Apache error.log
4. **Contact system admin** - Provide screenshots at error messages

---

**Version:** 1.0  
**Last Updated:** October 14, 2026  
**Author:** IPCR System Development Team
