{{-- Expects: form id notificationForm, action notifications.store --}}
@csrf

<div class="mb-4">
    <label for="notification_type" class="form-label fw-semibold">Notification Type <span class="text-danger">*</span></label>
    <select class="form-select" id="notification_type" name="notification_type" required>
        <option value="">Select notification type...</option>
        <option value="health_alert">🚨 Health Alert</option>
        <option value="announcement">📢 Announcement</option>
        <option value="reminder">📝 Reminder</option>
        <option value="vaccination_update">💉 Vaccination Update</option>
        <option value="clinic_schedule_update">🏥 Clinic Schedule Update</option>
    </select>
</div>

<div class="mb-4">
    <label for="title" class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
    <input type="text" class="form-control form-control-lg" id="title" name="title" value="{{ old('title') }}" required maxlength="255">
</div>

<div class="mb-4">
    <label for="message" class="form-label fw-semibold">Message <span class="text-danger">*</span></label>
    <textarea class="form-control" id="message" name="message" rows="6" required maxlength="2000">{{ old('message') }}</textarea>
    <small class="text-muted">Maximum 2000 characters</small>
</div>

<div class="mb-4">
    <label class="form-label fw-semibold mb-3">Target Audience <span class="text-danger">*</span></label>
    <div class="row g-2">
        <div class="col-md-6">
            <div class="form-check p-3 border rounded mb-2 target-audience-option d-flex align-items-center">
                <input class="form-check-input me-3" type="radio" name="target_audience" id="target_all" value="all_residents" required @checked(old('target_audience') === 'all_residents')>
                <label class="form-check-label flex-grow-1 mb-0" for="target_all"><strong>All residents</strong></label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-check p-3 border rounded mb-2 target-audience-option d-flex align-items-center">
                <input class="form-check-input me-3" type="radio" name="target_audience" id="target_purok" value="specific_purok" required @checked(old('target_audience') === 'specific_purok')>
                <label class="form-check-label flex-grow-1 mb-0" for="target_purok"><strong>Specific purok</strong></label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-check p-3 border rounded mb-2 target-audience-option d-flex align-items-center">
                <input class="form-check-input me-3" type="radio" name="target_audience" id="target_audience_age_group" value="specific_age_group" required @checked(old('target_audience') === 'specific_age_group')>
                <label class="form-check-label flex-grow-1 mb-0" for="target_audience_age_group"><strong>Specific age group</strong></label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-check p-3 border rounded mb-2 target-audience-option d-flex align-items-center">
                <input class="form-check-input me-3" type="radio" name="target_audience" id="target_pregnant" value="pregnant_women" required @checked(old('target_audience') === 'pregnant_women')>
                <label class="form-check-label flex-grow-1 mb-0" for="target_pregnant"><strong>Pregnant women</strong></label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-check p-3 border rounded mb-2 target-audience-option d-flex align-items-center">
                <input class="form-check-input me-3" type="radio" name="target_audience" id="target_senior" value="senior_citizens" required @checked(old('target_audience') === 'senior_citizens')>
                <label class="form-check-label flex-grow-1 mb-0" for="target_senior"><strong>Senior citizens</strong></label>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-check p-3 border rounded mb-2 target-audience-option d-flex align-items-center">
                <input class="form-check-input me-3" type="radio" name="target_audience" id="target_children" value="children_0_12" required @checked(old('target_audience') === 'children_0_12')>
                <label class="form-check-label flex-grow-1 mb-0" for="target_children"><strong>Children (0-12)</strong></label>
            </div>
        </div>
    </div>
</div>

<div id="purokField" class="mb-4" style="display: none;">
    <label for="target_purok" class="form-label fw-semibold">Purok Name <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="target_purok" name="target_purok" value="{{ old('target_purok') }}" placeholder="e.g., Purok Sunflower">
</div>

<div id="ageGroupField" class="mb-4" style="display: none;">
    <label for="target_age_group_input" class="form-label fw-semibold">Age Group <span class="text-danger">*</span></label>
    <input type="text" class="form-control" id="target_age_group_input" name="target_age_group" value="{{ old('target_age_group') }}" placeholder="e.g., 18-30, 31-50, etc.">
</div>

<div class="d-grid">
    <button type="submit" class="btn btn-primary btn-lg" id="sendBtn" disabled>Send Notification</button>
</div>
