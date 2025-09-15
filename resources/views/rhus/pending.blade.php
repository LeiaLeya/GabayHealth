@extends('layouts.app')

@section('content')
    <div class="pending-page-wrapper py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-xl-10">
                    <!-- Progress Timeline -->
                    <div class="progress-timeline mb-5">
                        <div class="timeline-step completed">
                            <div class="step-icon">
                                <i class="bi bi-check-circle-fill"></i>
                            </div>
                            <div class="step-label">Registration Submitted</div>
                        </div>
                        <div class="timeline-step active pulse">
                            <div class="step-icon">
                                <i class="bi bi-shield-check"></i>
                            </div>
                            <div class="step-label">Under Review</div>
                        </div>
                        <div class="timeline-step">
                            <div class="step-icon">
                                <i class="bi bi-building"></i>
                            </div>
                            <div class="step-label">Account Activation</div>
                        </div>
                    </div>

                    <!-- Main Content Card -->
                    <div class="status-container">
                        <div class="card main-card border-0">
                            <div class="row g-0">
                                <!-- Left Column - Status Information -->
                                <div class="col-lg-8">
                                    <div class="card-body p-lg-5">
                                        <div class="status-header mb-4">
                                            <h2 class="status-title">
                                                <span class="status-badge">
                                                    <i class="bi bi-clock-history"></i>
                                                </span>
                                                Registration Under Review
                                            </h2>
                                            <p class="status-subtitle">Your application is being carefully evaluated</p>
                                        </div>

                                        <div class="status-details">
                                            <div class="detail-item">
                                                <label>RHU Name</label>
                                                <h4>{{ $rhuData['name'] ?? 'N/A' }}</h4>
                                            </div>
                                            <div class="detail-item">
                                                <label>Current Status</label>
                                                <div class="status-pill">
                                                    <i class="bi bi-hourglass-split"></i>
                                                    {{ ucfirst($rhuData['status'] ?? 'Pending') }}
                                                </div>
                                            </div>
                                        </div>

                                        <div class="status-message mt-4">
                                            <p class="message-text">
                                                Our administrative team is currently reviewing your Rural Health Unit
                                                registration.
                                                This process ensures the quality and integrity of our healthcare network.
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Right Column - Help & Support -->
                                <div class="col-lg-4 support-column">
                                    <div class="support-content p-4">
                                        <div class="support-header mb-4">
                                            <i class="bi bi-info-circle-fill"></i>
                                            <h5>What's Next?</h5>
                                        </div>

                                        <ul class="support-steps">
                                            <li>
                                                <i class="bi bi-envelope-check"></i>
                                                <span>Wait for approval confirmation</span>
                                            </li>
                                            <li>
                                                <i class="bi bi-person-check"></i>
                                                <span>Complete necessary information</span>
                                            </li>
                                            <li>
                                                <i class="bi bi-box-arrow-in-right"></i>
                                                <span>Access your dashboard</span>
                                            </li>
                                        </ul>

                                        <div class="support-contact mt-4">
                                            <div class="contact-info">
                                                <h5 class="fw-bold">Contact support</h5>
                                                <div class="mb-2">
                                                    <i class="bi bi-telephone me-2"></i>
                                                    <span>09952112313</span>
                                                </div>
                                                <div>
                                                    <i class="bi bi-envelope me-2"></i>
                                                    <span>gabayHealth@gmail.com</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        .pending-page-wrapper {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            min-height: 80vh;
        }

        /* Progress Timeline Styles */
        .progress-timeline {
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: relative;
            padding: 0 2rem;
        }

        .progress-timeline::before {
            content: '';
            position: absolute;
            top: 2rem;
            left: 4rem;
            right: 4rem;
            height: 2px;
            background: #dee2e6;
            z-index: 1;
        }

        .timeline-step {
            position: relative;
            z-index: 2;
            text-align: center;
            flex: 1;
        }

        .step-icon {
            width: 4rem;
            height: 4rem;
            background: white;
            border: 2px solid #dee2e6;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
            color: #6c757d;
            transition: all 0.3s ease;
        }

        .step-label {
            font-size: 0.875rem;
            color: #6c757d;
            font-weight: 500;
        }

        .timeline-step.completed .step-icon {
            background: #198754;
            border-color: #198754;
            color: white;
        }

        .timeline-step.active .step-icon {
            background: #fff;
            border-color: #0d6efd;
            color: #0d6efd;
        }

        .timeline-step.active.pulse .step-icon {
            animation: pulse 2s infinite;
        }

        /* Main Card Styles */
        .main-card {
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.08);
            border-radius: 1rem;
            overflow: hidden;
        }

        .status-header {
            border-bottom: 1px solid #e9ecef;
            padding-bottom: 1.5rem;
        }

        .status-title {
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 1.75rem;
            color: #212529;
            margin-bottom: 0.5rem;
        }

        .status-badge {
            width: 3rem;
            height: 3rem;
            background: rgba(13, 110, 253, 0.1);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.25rem;
            color: #0d6efd;
        }

        .status-subtitle {
            color: #6c757d;
            font-size: 1.1rem;
            margin: 0;
        }

        .status-details {
            display: grid;
            gap: 2rem;
            margin-top: 2rem;
        }

        .detail-item label {
            text-transform: uppercase;
            font-size: 0.75rem;
            font-weight: 600;
            color: #6c757d;
            letter-spacing: 0.5px;
        }

        .detail-item h4 {
            margin: 0.5rem 0 0;
            color: #212529;
            font-weight: 600;
        }

        .status-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: #ffc107;
            color: #000;
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-weight: 500;
            font-size: 0.875rem;
        }

        /* Support Column Styles */
        .support-column {
            background: #f8f9fa;
            border-left: 1px solid #e9ecef;
        }

        .support-header {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            color: #0d6efd;
        }

        .support-header i {
            font-size: 1.5rem;
        }

        .support-header h5 {
            margin: 0;
            font-weight: 600;
        }

        .support-steps {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .support-steps li {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 1rem 0;
            border-bottom: 1px solid #e9ecef;
            color: #495057;
        }

        .support-steps li:last-child {
            border-bottom: none;
        }

        .support-steps li i {
            color: #0d6efd;
        }

        /* Animations */
        @keyframes pulse {
            0% {
                box-shadow: 0 0 0 0 rgba(13, 110, 253, 0.4);
            }

            70% {
                box-shadow: 0 0 0 10px rgba(13, 110, 253, 0);
            }

            100% {
                box-shadow: 0 0 0 0 rgba(13, 110, 253, 0);
            }
        }

        /* Responsive Adjustments */
        @media (max-width: 991.98px) {
            .support-column {
                border-left: none;
                border-top: 1px solid #e9ecef;
            }
        }

        @media (max-width: 767.98px) {
            .progress-timeline {
                flex-direction: column;
                gap: 2rem;
                padding: 0;
            }

            .progress-timeline::before {
                top: 0;
                bottom: 0;
                left: 2rem;
                right: auto;
                width: 2px;
                height: auto;
            }

            .timeline-step {
                width: 100%;
                display: flex;
                align-items: center;
                gap: 1rem;
            }

            .step-icon {
                margin: 0;
            }

            .step-label {
                text-align: left;
            }
        }
    </style>
@endsection
