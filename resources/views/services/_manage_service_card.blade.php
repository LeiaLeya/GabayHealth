{{-- Single service card for services management (BHC / RHU). Expects $service. --}}
<div class="service-card {{ !($service['is_active'] ?? true) ? 'suspended' : '' }}">
    <div class="service-card-header">
        <div class="d-flex justify-content-between align-items-start">
            <div class="flex-grow-1">
                <h6 class="mb-1">
                    {{ $service['display_name'] ?? $service['name'] }}
                    @if(!($service['is_active'] ?? true))
                        <span class="badge badge-suspended ms-2">
                            <i class="bi bi-pause-circle me-1"></i>Suspended
                        </span>
                    @endif
                </h6>
                @if(!($service['is_active'] ?? true) && !empty($service['deactivation_reason'] ?? ''))
                    <div class="text-warning small mb-2">
                        <i class="bi bi-info-circle me-1"></i>
                        Reason: {{ $service['deactivation_reason'] }}
                    </div>
                @endif
                <div class="d-flex gap-2 align-items-center">
                    <span class="badge bg-light text-dark">{{ $service['category'] }}</span>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <div class="toggle-switch me-2" title="{{ ($service['is_active'] ?? true) ? 'Disable Service' : 'Enable Service' }}">
                    <input type="checkbox"
                           id="toggle-{{ $service['id'] }}"
                           class="toggle-input"
                           {{ ($service['is_active'] ?? true) ? 'checked' : '' }}
                           onchange="handleToggleChange('{{ $service['id'] }}', '{{ $service['display_name'] ?? $service['name'] }}', this.checked)">
                    <label for="toggle-{{ $service['id'] }}" class="toggle-label">
                        <span class="toggle-inner"></span>
                        <span class="toggle-switch-slider"></span>
                    </label>
                </div>
            </div>
        </div>
    </div>
    <div class="service-card-body">
        @if($service['description'] ?? null)
            <p class="text-muted mb-3">{{ $service['description'] }}</p>
        @endif

        @if(isset($service['schedule']) && !empty($service['schedule']))
            <div class="mb-3">
                <h6 class="text-dark mb-2">
                    <i class="bi bi-clock me-2"></i>Schedule
                </h6>
                @foreach($service['schedule'] as $day => $times)
                    @if(!empty($times))
                        <div class="mb-2">
                            <strong class="text-capitalize">{{ ucfirst($day) }}:</strong>
                            @foreach($times as $time)
                                <span class="schedule-badge">{{ $time }}</span>
                            @endforeach
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <div class="mb-3">
                <span class="text-muted">
                    <i class="bi bi-clock me-1"></i>No schedule set
                </span>
            </div>
        @endif

        <div class="action-buttons">
            <button type="button"
                    class="action-btn edit-btn"
                    onclick="editService('{{ $service['id'] }}', '{{ $service['display_name'] ?? $service['name'] }}', '{{ $service['category'] }}', '{{ $service['description'] ?? '' }}', {{ json_encode($service['schedule'] ?? []) }})"
                    title="Edit">
                <i class="bi bi-pencil"></i>
            </button>
            <button type="button"
                    class="action-btn delete-btn"
                    onclick="deleteService('{{ $service['id'] }}', '{{ $service['display_name'] ?? $service['name'] }}')"
                    title="Delete">
                <i class="bi bi-trash"></i>
            </button>
        </div>
    </div>
</div>
